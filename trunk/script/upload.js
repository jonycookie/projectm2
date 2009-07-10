var Swiff = function(source, props){
	if (!Swiff.fixed) Swiff.fix();
	var instance = Swiff.nextInstance();
	Swiff.vars[instance] = {};
	props = $merge({
		width: 1,
		height: 1,
		id: instance,
		wmode: 'transparent',
		bgcolor: '#ffffff',
		allowScriptAccess: 'sameDomain',
		callBacks: {'onLoad': Class.empty},
		params: false
	}, props || {});
	var append = [];
	for (var p in props.callBacks){
		Swiff.vars[instance][p] = props.callBacks[p];
		append.push(p + '=Swiff.vars.' + instance + '.' + p);
	}
	if (props.params) append.push(Object.toQueryString(props.params));
	var swf = source + '?' + append.join('&');
	if (!document.getElementById('ajaxupload')) {
		return new Element('div').setHTML(
				'<object width="', props.width, '" height="', props.height, '" id="', props.id, '" type="application/x-shockwave-flash" data="', swf, '">'
					,'<param name="allowScriptAccess" value="', props.allowScriptAccess, '" />'
					,'<param name="movie" value="', swf, '" />'
					,'<param name="bgcolor" value="', props.bgcolor, '" />'
					,'<param name="scale" value="noscale" />'
					,'<param name="salign" value="lt" />'
					,'<param name="wmode" value="', props.wmode, '" />'
				,'</object>').firstChild;
	}else {
		return document.getElementById('Swiff1');
	}
	
};

Swiff.extend = $extend;

Swiff.extend({

	count: 0,

	callBacks: {},

	vars: {},

	nextInstance: function(){
		return 'Swiff' + Swiff.count++;
	},

	//from swfObject, fixes bugs in ie+fp9
	fix: function(){
		Swiff.fixed = true;
		window.addEvent('beforeunload', function(){
			__flash_unloadHandler = __flash_savedUnloadHandler = Class.empty;
		});
		if (!window.ie) return;
		window.addEvent('unload', function(){
			$each(document.getElementsByTagName("object"), function(swf){
				swf.style.display = 'none';
				for (var p in swf){
					if (typeof swf[p] == 'function') swf[p] = Class.empty;
				}
			});
		});
	},

	/*
	Function: Swiff.getVersion
		gets the major version of the flash player installed.

	Returns:
		a number representing the flash version installed, or 0 if no player is installed.
	*/

	getVersion: function(){
		if (!Swiff.pluginVersion) {
			var x;
			if(navigator.plugins && navigator.mimeTypes.length){
				x = navigator.plugins["Shockwave Flash"];
				if(x && x.description) x = x.description;
			} else if (window.ie){
				try {
					x = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
					x = x.GetVariable("$version");
				} catch(e){}
			}
			Swiff.pluginVersion = ($type(x) == 'string') ? parseInt(x.match(/\d+/)[0]) : 0;
		}
		return Swiff.pluginVersion;
	},

	/*
	Function: Swiff.remote
		Calls an ActionScript function from javascript. Requires ExternalInterface.

	Returns:
		Whatever the ActionScript Returns
	*/

	remote: function(obj, fn){
		var rs = obj.CallFunction("<invoke name=\"" + fn + "\" returntype=\"javascript\">" + __flash__argumentsToXML(arguments, 2) + "</invoke>");
		return eval(rs);
	}

});


Swiff.Uploader = new Class({

	options: {
		types: false,
		multiple: true,
		queued: true,
		swf: null,
		url: null,
		container: null
	},

	callBacks: {
		onOpen: Class.empty,
		onProgress: Class.empty,
		onSelect: Class.empty,
		onComplete: Class.empty,
		onError: Class.empty,
		onCancel: Class.empty
	},

	initialize: function(callBacks, onLoaded, options){
		if (Swiff.getVersion() < 8) return false;
		this.setOptions(options);
		this.onLoaded = onLoaded;
		var calls = $extend($merge(this.callBacks), callBacks || {});
		for (p in calls) calls[p] = calls[p].bind(this);
		this.instance = Swiff.nextInstance();
		Swiff.callBacks[this.instance] = calls;
		this.object = Swiff.Uploader.register(this.loaded.bind(this), this.options.swf, this.options.container);
		return this;
	},

	loaded: function(){
		Swiff.remote(this.object, 'create', this.instance, this.options.types, this.options.multiple, this.options.queued, this.options.url);
		this.onLoaded.delay(10);
	},

	browse: function(){
		Swiff.remote(this.object, 'browse', this.instance);
	},

	send: function(url){
		Swiff.remote(this.object, 'upload', this.instance, url);
	},

	remove: function(name, size){
		Swiff.remote(this.object, 'remove', this.instance, name, size);
	},

	fileIndex: function(name, size){
		return Swiff.remote(this.object, 'fileIndex', this.instance, name, size);
	},

	fileList: function(){
		return Swiff.remote(this.object, 'filelist', this.instance);
	}

});

Swiff.Uploader.implement(new Options);

Swiff.Uploader.extend = $extend;

Swiff.Uploader.extend({

	swf: 'Swiff.Uploader.swf',

	callBacks: [],

	register: function(callBack, url, container){
		if (!Swiff.Uploader.object || !Swiff.Uploader.loaded) {
			Swiff.Uploader.callBacks.push(callBack);
			if (!Swiff.Uploader.object) {
				Swiff.Uploader.object = new Swiff(url || Swiff.Uploader.swf, {callBacks: {'onLoad': Swiff.Uploader.onLoad}});
				(container || document.body).appendChild(Swiff.Uploader.object);
			}
		}
		else callBack.delay(10);
		return Swiff.Uploader.object;
	},

	onLoad: function(){
		Swiff.Uploader.loaded = true;
		Swiff.Uploader.callBacks.each(function(fn){
			fn.delay(10);
		});
		Swiff.Uploader.callBacks.length = 0;
	}

});


var FancyUpload = new Class({

	options: {
		url: false,
		swf: 'Swiff.Uploader.swf',
		multiple: true,
		queued: true,
		types: {'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'},
		limitSize: false,
		limitFiles: false,
		createReplacement: null,
		instantStart: false,
		allowDuplicates: false,
		optionFxDuration: 250,
		container: null,
		queueList: 'photoupload-queue',
		onComplete: Class.empty,
		onError: Class.empty,
		onCancel: Class.empty,
		onAllComplete: Class.empty
	},

	initialize: function(el, options){
		this.element = $(el);
		this.form = this.element.form;
		this.setOptions(options);
		this.fileList = [];

		this.uploader = new Swiff.Uploader({
			onOpen: this.onOpen.bind(this),
			onProgress: this.onProgress.bind(this),
			onComplete: this.onComplete.bind(this),
			onError: this.onError.bind(this),
			onSelect: this.onSelect.bind(this)
		}, this.initializeFlash.bind(this), {
			swf: this.options.swf,
			types: this.options.types,
			multiple: this.options.multiple,
			queued: this.options.queued,
			container: this.options.container
		});
	},

	initializeFlash: function() {
		this.queue = $(this.options.queueList);
		$(this.element.form).addEvent('submit', this.upload.bindWithEvent(this));
		if (this.options.createReplacement) this.options.createReplacement(this.element);
		else {
			
			new Element('input', {
				type: 'button',
				value: sBrowseCaption,
				events: {
					click: this.browse.bind(this)
				}
			}).injectBefore(this.element);
			this.element.remove();
		}

	},

	browse: function() {
		this.uploader.browse();
	},

	upload: function(e) {
		if (e) e.stop();
		url = this.options.url || this.form.action || location.href;
		this.uploader.send(url+'&format=json');
	},

	onSelect: function(name, size) {
		if (this.uploadTimer) this.uploadTimer = $clear(this.uploadTimer);
		if ((this.options.limitSize && (size > this.options.limitSize))
			|| (this.options.limitFiles && (this.fileList.length >= this.options.limitFiles))
			|| (!this.options.allowDuplicates && this.findFile(name, size) != -1)) return false;
		this.addFile(name, size);
		if (this.options.instantStart) this.uploadTimer = this.upload.delay(250, this);
		return true;
	},

	onOpen: function(name, size) {
		var index = this.findFile(name, size);
		this.fileList[index].status = 1;
		if (this.fileList[index].fx) return;
		this.fileList[index].fx = new Element('div', {'class': 'queue-subloader'}).injectInside(
				/*new Element('div', {'class': 'queue-loader'}).setHTML('Uploading').injectInside*/(this.fileList[index].element)
			).effect('width', {
				duration: 200,
				wait: false,
				unit: '%',
				transition: Fx.Transitions.linear
			}).set(0);
	},

	onProgress: function(name, bytes, total, percentage) {
		this.uploadStatus(name, total, percentage);
	},

	onComplete: function(name, size) {
		var index = this.uploadStatus(name, size, 100);
		this.fileList[index].fx.element.setHTML('Completed');
		this.fileList[index].status = 2;
		this.highlight(index, 'e1ff80');
		this.checkComplete(name, size, 'onComplete');
	},

	/**
	 * Error codes are just examples, customize them according to your server-errorhandling
	 *
	 */
	onError: function(name, size, error) {
		var msg = "Upload failed (" + error + ")";
		switch(error.toInt()) {
			case 500: msg = "Internal server error, please contact Administrator!"; break;
			case 400: msg = "Upload failed, please check your filesize!"; break;
			case 409: msg = "File already exists."; break;
			case 415: msg = "Unsupported this type."; break;
			case 412: msg = "water list of fonts not exist!"; break;
			case 417: msg = "Photo too small, please keep our photo manifest in mind!"; break;
		}
		var index = this.uploadStatus(name, size, 100);
		this.fileList[index].fx.element.setStyle('background-color', '#ffd780').setHTML(msg);
		this.fileList[index].status = 2;
		this.highlight(index, 'ffd780');
		this.checkComplete(name, size, 'onError');
	},

	checkComplete: function(name, size, fire) {
		this.fireEvent(fire, [name, size]);
		if (this.nextFile() == -1) this.fireEvent('onAllComplete');
	},

	addFile: function(name, size) {
		if (!this.options.multiple && this.fileList.length) this.remove(this.fileList[0].name, this.fileList[0].size);
		this.fileList.push({
			name: name,
			size: size,
			status: 0,
			percentage: 0,
			element: new Element('li').setHTML('<span class="queue-file">'+ name +'</span><span class="queue-size" title="'+ size +' byte">~'+ Math.ceil(size / 1000) +' kb</span>').injectInside(this.queue)
		});
		new Element('a', {
			href: 'javascript:void(0)',
			'class': 'input-delete',
			title: sRemoveToolTip,
			events: {
				click: this.cancelFile.bindWithEvent(this, [name, size])
			}
		}).injectBefore(this.fileList.getLast().element.getFirst());
		this.highlight(this.fileList.length - 1, 'e1ff80');
	},

	uploadStatus: function(name, size, percentage) {
		var index = this.findFile(name, size);
		this.fileList[index].fx.start(percentage).element.setHTML(percentage +'%');
		this.fileList[index].percentage = percentage;
		return index;
	},

	uploadOverview: function() {
		var l = this.fileList.length, i = -1, percentage = 0;
		while (++i < l) percentage += this.fileList[i].percentage;
		return Math.ceil(percentage / l);
	},

	highlight: function(index, color) {
		return this.fileList[index].element.effect('background-color', {duration: this.options.optionFxDuration}).start(color, 'fff');
	},

	cancelFile: function(e, name, size) {
		e.stop();
		this.remove(name, size);
	},

	remove: function(name, size, index) {
		if (name) index = this.findFile(name, size);
		if (index == -1) return;
		if (this.fileList[index].status < 2) {
			this.uploader.remove(name, size);
			this.checkComplete(name, size, 'onCancel');
		}
		this.fileList[index].element.effect('opacity', {duration: this.options.optionFxDuration}).start(1, 0).chain(Element.remove.pass([this.fileList[index].element], Element));
		this.fileList.splice(index, 1);
		return;
	},

	findFile: function(name, size) {
		var l = this.fileList.length, i = -1;
		while (++i < l) if (this.fileList[i].name == name && this.fileList[i].size == size) return i;
		return -1;
	},

	nextFile: function() {
		var l = this.fileList.length, i = -1;
		while (++i < l) if (this.fileList[i].status != 2) return i;
		return -1;
	},

	clearList: function(complete) {
		var i = -1;
		while (++i < this.fileList.length) if (complete || this.fileList[i].status == 2) this.remove(0, 0, 0, i--);
	}
});

FancyUpload.implement(new Events, new Options);