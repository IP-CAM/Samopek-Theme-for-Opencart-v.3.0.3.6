  var divs = document.getElementsByClassName("product-thumb");
  console.log(divs.toString());
  for (var i = 0; i < divs.length; i++) {
    console.log(divs[i].toString());
    var caption = divs[i].getElementsByClassName("caption");
    var a = caption[0].getElementsByTagName("h4")[0].getElementsByTagName("a")[0];
    if (a.innerHTML.length > 30) {
      a.innerHTML = a.innerHTML.substring(0, 30) + '...';
    } else {
      var l = a.innerHTML.length
      for (var j = 0; j < (30 - l); j++ ) {
        a.innerHTML = a.innerHTML + '&nbsp;';
      }
    }
  }