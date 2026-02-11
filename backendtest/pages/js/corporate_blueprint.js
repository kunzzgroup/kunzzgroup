                // 从PHP传递的战略目标数据
                
                // 转换数据格式以匹配原有结构
                const formattedStrategiesData = strategiesData.map((obj, index) => ({
                    deptName: 'S' + (index + 1) + '-' + (obj.department || ''),
                    deptDisplay: obj.department || '',
                    strategy: obj.strategy || '',
                    department: obj.department || '',
                    pic: obj.pic || '',
                    startDate: obj.startDate || '',
                    endDate: obj.endDate || '',
                    dashboardMetrics: obj.dashboardMetrics || [],
                    year: obj.year || ''
                }));
                
                function selectStrategy(index) {
                    const strategy = formattedStrategiesData[index];
                    if (!strategy) return;
                    
                    // 更新卡片状态
                    document.querySelectorAll('.strategy-card').forEach((card, i) => {
                        if (i === index) {
                            card.classList.add('active');
                            card.querySelector('.strategy-check').style.display = 'block';
                        } else {
                            card.classList.remove('active');
                            card.querySelector('.strategy-check').style.display = 'none';
                        }
                    });
                    
                    // 更新详细视图
                    const detailsEl = document.getElementById('strategicDetails');
                    detailsEl.classList.add('hidden');
                    
                    setTimeout(() => {
                        // 更新内容
                        document.getElementById('detailsTitle').textContent = strategy.strategy;
                        document.getElementById('detailsBadge').textContent = strategy.deptDisplay || strategy.deptName || 'Selected Pillar';
                        document.getElementById('picName').textContent = strategy.pic || '—';
                        
                        // 格式化日期
                        const formatDate = (dateStr) => {
                            if (!dateStr) return '—';
                            try {
                                const date = new Date(dateStr);
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate()).padStart(2, '0');
                                return `${year}-${month}-${day}`;
                            } catch (e) {
                                return dateStr;
                            }
                        };
                        
                        document.getElementById('startDate').textContent = formatDate(strategy.startDate);
                        document.getElementById('endDate').textContent = formatDate(strategy.endDate);
                        
                        // 更新指标
                        const metricsList = document.getElementById('measureList');
                        if (metricsList) {
                            if (strategy.dashboardMetrics && strategy.dashboardMetrics.length > 0) {
                                metricsList.innerHTML = strategy.dashboardMetrics.map(metric => 
                                    `<li class="measure-list-item">
                                        <div class="measure-dot"></div>
                                        <span class="measure-text">${metric}</span>
                                    </li>`
                                ).join('');
                            } else {
                                metricsList.innerHTML = '<li class="measure-list-item"><span class="measure-text">暂无指标</span></li>';
                            }
                        }
                        
                        detailsEl.classList.remove('hidden');
                    }, 300);
                }
                
                // 初始化动画
                document.addEventListener('DOMContentLoaded', function() {
                    const cards = document.querySelectorAll('.strategy-card');
                    cards.forEach((card, index) => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateX(-30px)';
                        setTimeout(() => {
                            card.style.transition = 'all 0.8s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateX(0)';
                        }, 300 + (index * 100));
                    });

                    // 更新策略总数
                    const countEl = document.getElementById('strategicListCount');
                    if (countEl) {
                        countEl.textContent = formattedStrategiesData.length.toString();
                    }
                });

        // 时间线动画控制器
        document.addEventListener('DOMContentLoaded', function() {
            const timelineWrapper = document.querySelector('.timeline-wrapper');
            if (!timelineWrapper) return;

            // 创建 IntersectionObserver 观察时间线容器
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // 触发时间线动画
                        animateTimeline(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.3,
                rootMargin: '0px 0px -100px 0px'
            });

            observer.observe(timelineWrapper);

            function animateTimeline(container) {
                // 1. 先绘制路径
                const routePath = container.querySelector('.map-route-path');
                if (routePath) {
                    setTimeout(() => {
                        routePath.classList.add('animate-in');
                    }, 200);
                }

                // 2. 逐个显示里程碑（按路径顺序）
                const milestones = container.querySelectorAll('.map-milestone');
                milestones.forEach((milestone, index) => {
                    setTimeout(() => {
                        milestone.classList.add('animate-in');
                    }, 1000 + (index * 200)); // 路径动画后开始显示里程碑
                });
            }

            // 添加里程碑悬停时的路径高亮效果
            const milestones = document.querySelectorAll('.map-milestone');
            milestones.forEach(milestone => {
                milestone.addEventListener('mouseenter', function() {
                    this.style.zIndex = '20';
                });
                milestone.addEventListener('mouseleave', function() {
                    this.style.zIndex = '10';
                });
            });

        });

        $(document).ready(function() {
            // 组织架构数据（已经是树形结构）
            
            if (!orgData) {
                console.error('组织架构数据为空');
                $('#orgchart-container').html('<p style="text-align: center; color: #6b7280; padding: 40px;">无法加载组织架构数据</p>');
                return;
            }
            
            console.log('组织架构数据:', orgData);
            
            // 初始化组织架构图 - OrgChart.js 使用树形结构
            $('#orgchart-container').orgchart({
                'data': orgData,
                'nodeContent': 'title',
                'nodeId': 'id',
                'pan': false,
                'zoom': false,
                'toggleSiblingsResp': true,
                'createNode': function($node, data) {
                    // 自定义节点样式
                    const level = data.level || '';
                    $node.addClass('level-' + level);
                    
                    // 自定义节点内容 - 显示职位和名字
                    const title = data.title || '—';
                    const name = data.name || '—';
                    
                    $node.html(
                        '<div class="orgchart-node-title">' + title + '</div>' +
                        '<div class="orgchart-node-content">' + name + '</div>'
                    );
                },
                'draggable': false,
                'direction': 't2b'
            });
            
            // 居中显示组织架构图
            setTimeout(function() {
                const orgchartEl = $('#orgchart-container .orgchart');
                if (orgchartEl.length) {
                    const containerWidth = $('#orgchart-container').width();
                    const chartWidth = orgchartEl.outerWidth();
                    if (chartWidth < containerWidth) {
                        const offsetLeft = (containerWidth - chartWidth) / 2;
                        orgchartEl.css('margin-left', offsetLeft + 'px');
                    }
                }
            }, 100);
        });

        // 存储所有部门的组织架构数据
        const initializedCharts = {}; // 记录已初始化的图表
        
        // 切换部门函数
        function switchInternalDept(deptIndex) {
            // 更新按钮状态
            $('.internal-dept-btn').removeClass('active');
            $('.internal-dept-btn[data-dept-index="' + deptIndex + '"]').addClass('active');
            
            // 更新图表显示
            $('.internal-dept-chart-wrapper').removeClass('active');
            $('.internal-dept-chart-wrapper[data-dept-index="' + deptIndex + '"]').addClass('active');
            
            // 如果该部门的图表还未初始化，则初始化它
            if (!initializedCharts[deptIndex] && internalOrgData[deptIndex]) {
                initializeDeptChart(deptIndex, internalOrgData[deptIndex]);
            }
        }
        
        // 初始化部门组织架构图
        function initializeDeptChart(index, deptTree) {
            const containerId = '#internal-dept-chart-' + index;
            const $container = $(containerId);
            
            if ($container.length === 0) {
                console.warn('容器不存在:', containerId);
                return;
            }
            
            // 初始化该部门的组织架构图
            $container.orgchart({
                'data': deptTree,
                'nodeContent': 'title',
                'nodeId': 'id',
                'pan': false,
                'zoom': false,
                'toggleSiblingsResp': true,
                'createNode': function($node, data) {
                    // 自定义节点样式
                    const level = data.level || '';
                    $node.addClass('level-' + level);
                    
                    // 自定义节点内容 - 显示职位和名字
                    const title = data.title || '—';
                    const name = data.name || '—';
                    
                    $node.html(
                        '<div class="orgchart-node-title">' + title + '</div>' +
                        '<div class="orgchart-node-content">' + name + '</div>'
                    );
                },
                'draggable': false,
                'direction': 't2b'
            });
            
            // 标记为已初始化
            initializedCharts[index] = true;
            
            // 居中显示该部门的组织架构图
            setTimeout(function() {
                const orgchartEl = $container.find('.orgchart');
                if (orgchartEl.length) {
                    const containerWidth = $container.width();
                    const chartWidth = orgchartEl.outerWidth();
                    if (chartWidth < containerWidth) {
                        const offsetLeft = (containerWidth - chartWidth) / 2;
                        orgchartEl.css('margin-left', offsetLeft + 'px');
                    }
                }
            }, 100);
        }
        
        $(document).ready(function() {
            if (!internalOrgData || internalOrgData.length === 0) {
                console.error('内部组织架构数据为空');
                $('#internal-orgchart-container').html('<p style="text-align: center; color: #6b7280; padding: 40px;">无法加载内部组织架构数据</p>');
                return;
            }
            
            console.log('内部组织架构数据:', internalOrgData);
            
            // 初始化第一个部门的组织架构图
            if (internalOrgData.length > 0) {
                initializeDeptChart(0, internalOrgData[0]);
            }
        });

        function alignScoringSections() {
            // 处理所有 culture-explanation-grid
            document.querySelectorAll('.culture-explanation-grid').forEach(function(grid) {
                const cards = grid.querySelectorAll('.culture-explanation-card');
                if (cards.length === 0) return;
                
                // 重置所有内容区域的高度，以便重新计算
                cards.forEach(function(card) {
                    const content = card.querySelector('.culture-explanation-content');
                    if (content) {
                        content.style.minHeight = 'auto';
                    }
                });
                
                // 强制重排以获取实际高度
                void grid.offsetHeight;
                
                // 找出所有解说内容区域的最大高度
                let maxHeight = 0;
                cards.forEach(function(card) {
                    const content = card.querySelector('.culture-explanation-content');
                    if (content) {
                        const height = content.offsetHeight;
                        if (height > maxHeight) {
                            maxHeight = height;
                        }
                    }
                });
                
                // 设置所有解说内容区域的最小高度为最大高度
                cards.forEach(function(card) {
                    const content = card.querySelector('.culture-explanation-content');
                    if (content) {
                        content.style.minHeight = maxHeight + 'px';
                    }
                });
            });
        }
        
        // 页面加载完成后执行
        document.addEventListener('DOMContentLoaded', function() {
            alignScoringSections();
        });
        
        // 窗口大小改变时重新对齐
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                alignScoringSections();
            }, 250);
        });

