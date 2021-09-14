var navToggle,
	container,
	navOverlay = document.createElement('div'),
	i,l;

function nav_toggleDrawer(e) {
	e.preventDefault();
	if (navToggle) {
		navToggle.classList.toggle('is-active');
	}
	document.body.classList.toggle('is-navOpen');
	//this.blur();
}

document.addEventListener('DOMContentLoaded', function() {
	navToggle = document.getElementById('NavToggle');
	container = document.getElementById('Page');

	// Click to open
	if (navToggle) {
		navToggle.addEventListener('click', nav_toggleDrawer);
	}

	// Add overlay
	if (container && navOverlay) {
		navOverlay.classList.add('NavOverlay');
		navOverlay.addEventListener('click', nav_toggleDrawer);
		container.appendChild(navOverlay);
	}

});
