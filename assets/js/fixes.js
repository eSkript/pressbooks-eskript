const $ = jQuery;

/**
 * Adjust color of LaTeX formula according to the local text color.
 */
$(document).ready(function() {
  const blackish = ['rgb(0, 0, 0)', 'rgb(31, 31, 29)'];
  $('img.latex').each(function(i, n) {
    var style = window.getComputedStyle(n);
    var color = style.getPropertyValue('color').replace(/\s*/, '');
    // Don't adjust custom colors.
    if (n.src.indexOf('&fg=') !== -1) {
      return;
    }
    // Don't replace almost black.
    var components = color.match(/\((.*)\)/)[1].split(',');
    var maxComp = Math.max.apply(Math, components);
    if (maxComp <= 32) {
      return;
    }
    // Request new image with right color.
    n.src = n.src + '&color=' + encodeURIComponent(color);
  });
});
