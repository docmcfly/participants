  let f = false
  $(".upcomingDateBefore").each(function() {
  	if( !f  ) {
  		$(this).get(0).scrollIntoView({ behavior: 'smooth' });
  		f = true
  	}
  })