$(function () {

  // 随滚动屏幕显隐导航
  $('.masthead').visibility({
    once: false,
    onBottomPassed: function () {
      $('.fixed.menu').transition('fade in');
    },
    onBottomPassedReverse: function () {
      $('.fixed.menu').transition('fade out');
    }
  });

  // 创建侧边栏并追加菜单项
  $('.ui.sidebar').sidebar('attach events', '.toc.item');

  // 初始化下拉菜单
  $('.ui.dropdown').dropdown();

  console.log("[OK] 页面初始化完成");

});