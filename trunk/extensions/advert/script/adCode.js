function ObjectAD() {
  /* Define Variables*/
  this.ADID        = 0;
  this.ADType      = 0;
  this.ADName      = "";
  this.ImgUrl      = "";
  this.ImgWidth    = 0;
  this.ImgHeight   = 0;
  this.FlashWmode  = 0;
  this.LinkUrl     = "";
  this.LinkTarget  = 0;
  this.LinkAlt     = "";
  this.Priority    = 0;
  this.CountView   = 0;
  this.CountClick  = 0;
  this.InstallDir  = "";
  this.ADDIR       = "";
  this.StartTime   = 0;
  this.EndTime     = 0;
  this.Cid         = "";
}

function CodeZoneAD(_id) {
  /* Define Common Variables*/
  this.ID          = _id;
  this.ZoneID      = 0;

  /* Define Unique Variables*/
  this.Active	   = 1;
  /* Define Objects */
  this.AllAD       = new Array();
  this.ShowAD      = null;

  /* Define Functions */
  this.AddAD       = CodeZoneAD_AddAD;
  this.GetShowAD   = CodeZoneAD_GetShowAD;
  this.Show        = CodeZoneAD_Show;

}

function CodeZoneAD_AddAD(_AD) {
  var now = new Date();
  var s = now.getTime();
  var Url=top.window.location.href;
  var cid=GetQueryValue(Url,"cid");
  if(cid=="")cid=0;
  cid = ","+cid+",";
  s = (s-s%1000)/1000;
  if (this.Active == 1 && s > _AD.StartTime && s < _AD.EndTime && _AD.Cid.indexOf(cid) != -1){
	this.AllAD[this.AllAD.length] = _AD;
  }
}

function CodeZoneAD_GetShowAD() {
  if (this.ShowType > 1) {
    this.ShowAD = this.AllAD[0];
    return;
  }
  var num = this.AllAD.length;
  var sum = 0;
  for (var i = 0; i < num; i++) {
    sum = sum + this.AllAD[i].Priority;
  }
  if (sum <= 0) {return ;}
  var rndNum = Math.random() * sum;
  i = 0;
  j = 0;
  while (true) {
    j = j + this.AllAD[i].Priority;
    if (j >= rndNum) {break;}
    i++;
  }
  this.ShowAD = this.AllAD[i];
}

function CodeZoneAD_Show() {
  if (!this.AllAD) {
    return;
  } else {
    this.GetShowAD();
  }

  if (this.ShowAD == null) return false;
  if (this.ShowAD.ADType == 1 || this.ShowAD.ADType == 2 || this.ShowAD.ADType == 3 || this.ShowAD.ADType == 5 ){
		if (this.ShowAD.LinkUrl) {
			str = "<a href='" + this.ShowAD.LinkUrl + "' target='" + ((this.ShowAD.LinkTarget == 0) ? "_self" : "_blank") + "' title='" + this.ShowAD.LinkAlt + "'>" + this.ShowAD.ADIntro + "</a>";
		}else{
			str = this.ShowAD.ADIntro;
		}
  } else if (this.ShowAD.ADType == 4 ) {
    str = this.ShowAD.ADIntro ;
  }

  document.write(str);
}

function GetQueryValue(sorStr,panStr){
  var vStr="";
  if(sorStr==null || sorStr=="" || panStr==null || panStr=="")return vStr;
  sorStr = sorStr.toLowerCase();
  panStr += "=";
  var itmp=sorStr.indexOf(panStr);
  if (itmp<0){return vStr;}
  sorStr = sorStr.substr(itmp+panStr.length);
  itmp=sorStr.indexOf("&");
  if (itmp<0){
	return sorStr;
  }else{
	sorStr=sorStr.substr(0,itmp);
	return sorStr;
  }
}