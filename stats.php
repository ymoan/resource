<?php
// 设置UTF-8编码，支持中文显示
header('Content-Type: text/html; charset=utf-8');

// 定义图片文件扩展名
$imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

// 获取当前文件所在目录
$currentDir = dirname(__FILE__) . '/';

// 存储所有文件夹及其图片数量
$folderImageCounts = [];
$totalImages = 0;

/**
 * 递归统计目录中的图片数量
 * @param string $dir 目录路径
 * @return int 图片数量
 */
function countImagesInDir($dir, $imageExtensions) {
    $count = 0;
    
    // 检查目录是否存在且可访问
    if (!is_dir($dir) || !is_readable($dir)) {
        return 0;
    }
    
    $directory = new DirectoryIterator($dir);
    
    foreach ($directory as $fileinfo) {
        if ($fileinfo->isDot()) {
            continue;
        }
        
        // 如果是目录，递归处理
        if ($fileinfo->isDir()) {
            $subDir = $fileinfo->getPathname() . '/';
            $subCount = countImagesInDir($subDir, $imageExtensions);
            $count += $subCount;
        } 
        // 如果是文件，检查是否为图片
        elseif ($fileinfo->isFile()) {
            $extension = strtolower($fileinfo->getExtension());
            if (in_array($extension, $imageExtensions)) {
                $count++;
            }
        }
    }
    
    return $count;
}

/**
 * 获取所有同级子目录并统计图片数量
 */
function getFolderImageCounts($currentDir, $imageExtensions, &$totalImages) {
    $folders = [];
    
    $directory = new DirectoryIterator($currentDir);
    
    foreach ($directory as $fileinfo) {
        if ($fileinfo->isDot()) {
            continue;
        }
        
        if ($fileinfo->isDir()) {
            $folderName = $fileinfo->getFilename();
            $folderPath = $fileinfo->getPathname() . '/';
            
            // 统计该目录中的图片数量
            $imageCount = countImagesInDir($folderPath, $imageExtensions);
            
            if ($imageCount > 0) {
                $folders[] = [
                    'name' => $folderName,
                    'path' => $folderPath,
                    'count' => $imageCount
                ];
                $totalImages += $imageCount;
            }
        }
    }
    
    // 按文件夹名排序
    usort($folders, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    return $folders;
}

// 获取所有文件夹的图片数量
$folderImageCounts = getFolderImageCounts($currentDir, $imageExtensions, $totalImages);
$totalFolders = count($folderImageCounts);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>角色图片数量统计</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    
    <!-- 配置Tailwind自定义颜色和字体 -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#10B981',
                        accent: '#8B5CF6',
                        dark: '#1E293B',
                        light: '#F8FAFC'
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .folder-card {
                @apply bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100;
            }
            .folder-card:hover {
                @apply transform -translate-y-1;
            }
            .stat-card {
                @apply bg-gradient-to-br rounded-xl shadow-md p-6 text-white;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- 页面标题 -->
        <header class="text-center mb-12">
            <h1 class="text-[clamp(2rem,5vw,3.5rem)] font-bold text-dark mb-4">
                <i class="fa fa-picture-o text-primary mr-3"></i>当前角色图数量统计
            </h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                统计当前目录下所有子文件夹中的图片数量，包含各种命名格式的文件夹
            </p>
        </header>
        
        <!-- 统计概览 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="stat-card from-primary to-blue-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">总图片数量</p>
                        <h3 class="text-3xl font-bold mt-1"><?php echo $totalImages; ?></h3>
                    </div>
                    <div class="bg-white/20 p-4 rounded-full">
                        <i class="fa fa-image text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card from-secondary to-emerald-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-sm font-medium">包含图片的文件夹</p>
                        <h3 class="text-3xl font-bold mt-1"><?php echo $totalFolders; ?></h3>
                    </div>
                    <div class="bg-white/20 p-4 rounded-full">
                        <i class="fa fa-folder-open text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card from-accent to-purple-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">统计时间</p>
                        <h3 class="text-3xl font-bold mt-1"><?php echo date('Y-m-d H:i'); ?></h3>
                    </div>
                    <div class="bg-white/20 p-4 rounded-full">
                        <i class="fa fa-clock-o text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 图表展示 -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-10">
            <h2 class="text-xl font-bold text-gray-800 mb-4">各文件夹图片数量分布</h2>
            <div class="h-80">
                <canvas id="imageDistributionChart"></canvas>
            </div>
        </div>
        
        <!-- 文件夹列表 -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fa fa-folder text-primary mr-2"></i>文件夹图片数量
            </h2>
            
            <?php if (empty($folderImageCounts)): ?>
                <div class="bg-white rounded-xl shadow-md p-8 text-center">
                    <i class="fa fa-search text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">未找到包含图片的文件夹</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($folderImageCounts as $folder): ?>
                        <div class="folder-card">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <i class="fa fa-folder text-yellow-500 text-2xl mr-3"></i>
                                        <h3 class="font-semibold text-gray-800 truncate max-w-[200px]">
                                            <?php echo htmlspecialchars($folder['name']); ?>
                                        </h3>
                                    </div>
                                    <span class="bg-primary/10 text-primary text-sm font-medium px-2.5 py-0.5 rounded-full">
                                        <?php echo $folder['count']; ?> 张
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <?php 
                                    $percentage = $totalImages > 0 ? min(100, ($folder['count'] / $totalImages) * 100) : 0;
                                    // 生成随机但柔和的颜色
                                    $hue = ($folder['count'] * 37) % 360; // 使用质数37确保分布更均匀
                                    ?>
                                    <div class="bg-[hsl(<?php echo $hue; ?>,70%,50%)] h-2.5 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">占总数的 <?php echo number_format($percentage, 1); ?>%</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 页脚 -->
        <footer class="text-center text-gray-500 text-sm py-6">
            <p>角色图片统计工具 &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>

    <script>
        // 等待DOM加载完成
        document.addEventListener('DOMContentLoaded', function() {
            // 准备图表数据
            const folderNames = <?php 
                echo json_encode(array_map(function($item) {
                    return htmlspecialchars($item['name']);
                }, $folderImageCounts));
            ?>;
            
            const imageCounts = <?php 
                echo json_encode(array_column($folderImageCounts, 'count'));
            ?>;
            
            // 生成颜色数组
            const backgroundColors = imageCounts.map((count, index) => {
                const hue = (count * 37 + index * 13) % 360; // 增加index偏移使颜色更分散
                return `hsla(${hue}, 70%, 60%, 0.7)`;
            });
            
            const borderColors = backgroundColors.map(color => {
                return color.replace('0.7', '1');
            });
            
            // 创建图表
            if (folderNames.length > 0) {
                const ctx = document.getElementById('imageDistributionChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: folderNames,
                        datasets: [{
                            label: '图片数量',
                            data: imageCounts,
                            backgroundColor: backgroundColors,
                            borderColor: borderColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `图片数量: ${context.raw} 张`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // 添加文件夹卡片动画效果
            const folderCards = document.querySelectorAll('.folder-card');
            folderCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('scale-[1.02]');
                });
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('scale-[1.02]');
                });
            });
        });
    </script>
</body>
</html>
