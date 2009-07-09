function parseTemplate(datas,thisid,styleid){
	if(!datas) return false;
	var content = document.getElementById(styleid).innerHTML;
	var re	= /#([a-zA-Z\-_]+)#/g;
	var preg= content.match(re);
	var fields	= new Array();
	var field	= new Array();
	for(var i=0;i<preg.length;i++){
		fields[i]=(/#([a-zA-Z\-_]+)#/).exec(preg[i]);
		field[i] = fields[i][1];
	}
	var filldiv = '';
	var pat		= '';
	for(var i=0;i<datas.length;i++){
		pat	= content;
		for(var j=0;j<preg.length;j++){
			pat = pat.replace(preg[j],datas[i][field[j]]);
		}
		filldiv=filldiv+pat;
	}
	document.getElementById(thisid).innerHTML=filldiv;
}

if (!javascriptcn)
{
    var javascriptcn = {};
}
(function(){
javascriptcn.ready = function(){
    var load_events = [],
        load_timer,
        script,
        done,
        exec,
        old_onload,
        init = function () {
            done = true;
            clearInterval(load_timer);
            while (exec = load_events.shift())
                exec();
            if (script) script.onreadystatechange = '';
        };
    return function (func) {
        if (done) return func();
        if (!load_events[0]) {
            // for Mozilla/Opera9
            if (document.addEventListener)
                document.addEventListener("DOMContentLoaded", init, false);
            if (/WebKit/i.test(navigator.userAgent)) { // sniff
                load_timer = setInterval(function() {
                    if (/loaded|complete/.test(document.readyState))
                        init(); // call the onload handler
                }, 10);
            }
            old_onload = window.onload;
            window.onload = function() {
                init();
                if (old_onload) old_onload();
            };
        }
        load_events.push(func);
    }
}();
})();