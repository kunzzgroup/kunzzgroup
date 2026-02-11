// Initialize OrgChart
function initOrgChart(orgData) {
    $(document).ready(function () {
        if (!orgData) {
            console.error('组织架构数据为空');
            $('#orgchart').html('<p style="text-align: center; color: #6b7280; padding: 40px;">无法加载组织架构数据</p>');
            return;
        }

        console.log('组织架构数据:', orgData);

        // 初始化组织架构图 - OrgChart.js 使用树形结构
        $('#orgchart').orgchart({
            'data': orgData,
            'nodeContent': 'title',
            'nodeId': 'id',
            'pan': true,
            'zoom': true,
            'toggleSiblingsResp': true,
            'createNode': function ($node, data) {
                // 自定义节点样式
                const level = data.level || '';
                $node.addClass('level-' + level);

                // 自定义节点内容
                const title = data.title || '—';
                const name = data.name || '—';

                $node.html(
                    '<div class="node-title">' + title + '</div>' +
                    '<div class="node-content">' + name + '</div>'
                );
            }
        });
    });
}
