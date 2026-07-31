(function() {
  if (typeof Html5Qrcode === 'undefined') {
    var script = document.createElement('script');
    script.src = 'https://unpkg.com/html5-qrcode@2.3.7/html5-qrcode.min.js';
    script.integrity = 'sha384-hJMcc4vZxKbPwUvrjl/f7MWYnIQvANoP8ItXzz0nUy9i6D0ShaiIMj32mTZGb9kj';
    script.crossOrigin = 'anonymous';
    document.head.appendChild(script);
  }
})();
