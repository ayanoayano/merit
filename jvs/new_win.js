// javascript for other window opening.

function initExtLinks() {
	var links = getTagElements('a');
	for (var i = 0; i < links.length; i++) {
		var className = getClassname(links[i]);
		if (className && className.indexOf('blank') != -1) {
			setOpenFunc(links[i]);
		}
	}
}

function setOpenFunc(anc) {
	anc.onclick = function(event) {return doOpenWindow(this, event);};
}

function doOpenWindow(anc, event) {
	return !window.open(anc);
}

function getClassname(obj) {
	var classname;
	if (obj.className) {
		classname = obj.className;
	} else if (obj.getAttribute){
		classname = obj.getAttribute('class');
	}
	return classname;
}

function getEvent(event) {
	if (!event && window.event) {
		return window.event;
	} else {
		return event;
	}
}

function getTagElements(tagName) {
	var tags;
	if (document.getElementsByTagName) {
		tags = document.getElementsByTagName(tagName);
	}
	return tags;
}

if (window.attachEvent) {
	window.attachEvent('onload', initExtLinks);
} else if (window.addEventListener) {
	window.addEventListener('load', initExtLinks, true);
}

