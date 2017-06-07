const $ = jQuery;

/**
 * Adjust color of LaTeX formula according to the local text color.
 */
$(document).ready(function() {
  $('img.latex').each(function(i, n) {
    var style = window.getComputedStyle(n);
    var color = style.getPropertyValue('color');
    if (color != 'rgb(0, 0, 0)' && n.src.indexOf('&fg=') == -1) {
      n.src = n.src + '&color=' + encodeURIComponent(color);
    }
  });
});
