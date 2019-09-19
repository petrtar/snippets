var all = document.querySelectorAll('*');
all.forEach(item => {
  if (item.offsetWidth > window.innerWidth) {console.log(item) }});