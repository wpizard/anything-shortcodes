(function(){
	// Elements
	var fab = document.querySelector('.anys-fab');
	var sheet = document.getElementById('anys-mobile-menu');
	var closeBtn = sheet ? sheet.querySelector('.anys-mobile-sheet__close') : null;
	var backdrop = sheet ? sheet.querySelector('.anys-mobile-sheet__backdrop') : null;

	if(!fab || !sheet) return;

	// Open/close helpers
	function openSheet(){
		sheet.hidden = false;
		sheet.classList.add('is-open');
		fab.setAttribute('aria-expanded', 'true');
		// Focus first link for accessibility
		var firstLink = sheet.querySelector('a,button,[tabindex]:not([tabindex="-1"])');
		if(firstLink) firstLink.focus();
	}
	function closeSheet(){
		sheet.classList.remove('is-open');
		fab.setAttribute('aria-expanded', 'false');
		// Use a microtask so CSS can apply before hiding
		setTimeout(function(){ sheet.hidden = true; }, 0);
	}

	// Toggle
	fab.addEventListener('click', function(){
		var expanded = fab.getAttribute('aria-expanded') === 'true';
		if(expanded) { closeSheet(); } else { openSheet(); }
	});

	// Close handlers
	if(closeBtn) closeBtn.addEventListener('click', closeSheet);
	if(backdrop) backdrop.addEventListener('click', closeSheet);
	document.addEventListener('keydown', function(e){
		if(e.key === 'Escape') closeSheet();
	});
})();
