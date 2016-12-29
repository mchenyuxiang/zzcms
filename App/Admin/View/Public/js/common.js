$('.table-responsive #zzcms-delete').on('click', function () {
    var id = $(this).attr('attr-id');
    var url = SCOPE.delete_url;
    data = {};
    data['id'] = id;
    layer.open({
        type: 0,
        title: '是否删除？',
        btn: ['yes', 'no'],
        icon: 3,
        closeBtn: 2,
        content: "是否确定删除?",
        scrollbar: true,
        yes: function () {
            // 执行相关跳转
            todelete(url, data);
            // layer.close( );
        },

    });
});
function todelete(url, data) {
    $.post(
        url,
        data,
        function (s) {
            if (s.status == 1) {
                return dialog.success('成功',s.url);
                // 跳转到相关页面
            } else {
                return dialog.error(s.message);
            }
        }, "JSON");
}