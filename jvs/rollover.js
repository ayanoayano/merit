// javascript sample for image preloading.

var loaded = false;
var overKey = "_f2";
function initPreloadImages() {
	if (loaded) { return; } else { loaded = true; }
	var images = getTagElements('img');
	for (var i = 0; i < images.length; i++) {
		var className = getClassname(images[i]);
		if (className && className.indexOf('preload') != -1) {
			setPreload(images[i]);
		}
	}
}

// javascript sample for swapping images when user focused.

function initRolloverAnchors() {
	var anchors = getTagElements('a');
	attachRollover(anchors, true);

	var images = getTagElements('img');
	attachRollover(images, false);
}

function attachRollover(objects, withFocus) {
	for (var i = 0; i < objects.length; i++) {
		var obj = objects[i];
		var className = getClassname(obj);
		if (className && className.indexOf('rollover') != -1) {
			if (obj.tagName.toLowerCase() == 'img') {
				setPreload(obj);
			} else if (obj.tagName.toLowerCase() == 'a') {
				setPreloads(obj.getElementsByTagName('img'));
			}
			objects[i].onmouseover
				= function(event) { swapImg(event, overKey);};
			objects[i].onmouseout 
				= function(event) { swapImg(event, overKey);};
			if (withFocus) {
				objects[i].onblur 
					= function(event) { swapImg(event, overKey);};
				objects[i].onfocus 
					= function(event) { swapImg(event, overKey);};
			}
		}
	}
}

function setPreload(img) {
	var suffix = getSuffix(img);
	var path = getBasename(img, overKey);
	(new Image()).src = path + overKey + suffix;
}

function setPreloads(images) {
	for (var i = 0; i < images.length; i++) {
		setPreload(images[i]);
	}
}

function swapImg(event, key) {
	var evt = getEvent(event);
	var img = getTarget(event);
	if (!img.src) return;
	var suffix = getSuffix(img);
	var path = getBasename(img, key);

	var newSrc;
	if (evt.type == 'mouseover' || evt.type == 'focus') {
		newSrc = path + key + suffix;
	} else if (evt.type == 'mouseout' || evt.type == 'blur') {
		newSrc = path + suffix;
	}
	img.src = newSrc;
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

function getBasename(img, key) {
	var path = img.src.substr(0, img.src.lastIndexOf('.'));
	if (path.lastIndexOf(key) == (path.length - key.length)) {
		path = path.substr(0, path.length - key.length);
	}
	return path;
}

function getSuffix(img) {
	var suffix = img.src.substr(img.src.lastIndexOf('.'));
	return suffix;
}

function getEvent(event) {
	return (!event && window.event) ? window.event : event;
}

function getTarget(event) {
	var evt = getEvent(event);
	return (evt.target) ? evt.target : evt.srcElement;
}

function getTagElements(tagName) {
	var tags;
	if (document.getElementsByTagName) {
		tags = document.getElementsByTagName(tagName);
	}
	return tags;
}

if (window.attachEvent) {
	window.attachEvent('onload', initRolloverAnchors);
	window.attachEvent('onload', initPreloadImages);
} else if (window.addEventListener){
	window.addEventListener('load', initRolloverAnchors, true);
	window.addEventListener('load', initPreloadImages, true);
}

