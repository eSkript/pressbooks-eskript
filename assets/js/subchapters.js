const section_order = [];
const section_map = {}; // map id to section id

let next_href = null;
let previous_href = null;

$(document).ready(function() {
  // initialize id / section lookups
  const content = $('.entry-content');
  if (content.length == 0) {
    // not a regular chapter; probably landing-page
    return;
  }
  if (content.children().first().get(0).tagName != 'H1') {
    // there is content before the first header
    section_order.push('');
  }
  let current_section = '';
  content.find('*').each(function(i) {
    const elem = $(this);
    const id = elem.attr('id');
    if (typeof id == 'undefined') return;
    const tag = $(this).get(0).tagName;
    if (tag == 'H1') {
      section_order.push(id);
      current_section = id;
    }
    section_map[id] = current_section;
  });
  // modify navigation
  
  next_href = $('.nav>.next>a').attr('href') || null;
  previous_href = $('.nav>.previous>a').attr('href') || null;
  
  subchapterize();
  window.addEventListener("hashchange", subchapterize, false);
});


function id_to_section_id(id) {
  if (section_map.hasOwnProperty(id)) {
    return section_map[id];
  } else {
    return section_order[0];
  }
}

function nav_href(section_id, d) {
  const pos = section_order.indexOf(section_id) + d;
  if (pos >= section_order.length) {
    if (next_href === null) return null;
    return next_href;
  }
  if (pos < 0) {
    if (previous_href === null) return null;
    return previous_href + '#last';
  }
  return '#' + section_order[pos];
}

// show and hide nodes according to window hash
function subchapterize() {
  const hash = window.location.hash;
  let hash_id = hash.substr(1);
  if (hash_id == 'last') {
    hash_id = section_order[section_order.length - 1];
  }
  const section_id = id_to_section_id(hash_id);
  let hide = section_id.length != 0;
  $('.entry-content').children().each(function(i) {
    const elem = $(this);
    const tag = elem.get(0).tagName;
    if (tag == 'H1' && !elem.hasClass("not-in-list")) {
      const id = elem.attr('id');
      hide = id !== section_id;
    }
    if (hide) {
      elem.hide();
    } else {
      elem.show();
    }
    // console.log(type, id);
  });
  // update links
  const nxt = nav_href(section_id, +1);
  const prv = nav_href(section_id, -1);
  const nav = $('#content>.nav').empty();
  if (prv) nav.append('<span class="previous"><a href="'+prv+'">Previous</a></span>');
  if (nxt) nav.append('<span class="next"><a href="'+nxt+'">Next</a></span>');
  // scroll
  const elm = document.getElementById(hash_id);
  const scrollTop = elm === null ? '0px' : $(elm).offset().top;
  $('html, body').animate({scrollTop: scrollTop}, 300);
}
