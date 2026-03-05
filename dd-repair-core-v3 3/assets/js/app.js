/**
 * DD Repair Core — app.js
 * defer で読み込み。レンダリングを一切ブロックしない。
 * PHPサーバーサイドレンダリングなのでJS最小限。
 */

// トースト自動非表示
(function() {
  var toast = document.querySelector('.toast');
  if (toast) {
    setTimeout(function() {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.5s';
      setTimeout(function() { toast.remove(); }, 500);
    }, 2500);
  }
})();

// フォームラジオボタン active クラス
(function() {
  document.querySelectorAll('.form-radio').forEach(function(label) {
    label.addEventListener('click', function() {
      var group = this.closest('.form-radio-group');
      if (group) {
        group.querySelectorAll('.form-radio').forEach(function(l) { l.classList.remove('active'); });
      }
      this.classList.add('active');
    });
  });
})();

// 検索: フォームsubmitで自動
(function() {
  var searchInputs = document.querySelectorAll('.search-input-wrap input');
  searchInputs.forEach(function(input) {
    var timer;
    input.addEventListener('input', function() {
      clearTimeout(timer);
      var form = input.closest('form');
      timer = setTimeout(function() { if (form) form.submit(); }, 600);
    });
  });
})();

// ヘッダー日付（サーバーレンダリング済みなので不要だが念のため）
(function() {
  var el = document.getElementById('header-date');
  if (el) {
    var now = new Date();
    var days = ['日','月','火','水','木','金','土'];
    el.textContent = now.getFullYear() + '年' + (now.getMonth()+1) + '月' + now.getDate() + '日（' + days[now.getDay()] + '）';
  }
})();
