if(document.all&&!document.getElementById){document.getElementById=function(A){return document.all[A]}}function BlockSubmit(B,C){var A=B.keyCode||B.which;if(A==13){C();return false}else{return true}}function CheckAll(B){var A=Explode(B,",");for(j=0;j<A.length;j++){CheckSwitch(A[j],true)}}function CheckNone(B){var A=Explode(B,",");for(j=0;j<A.length;j++){CheckSwitch(A[j],false)}}function CheckSwitch(C,A){var B=document.getElementsByTagName("input");for(i=0;i<B.length;i++){if(B[i].type=="checkbox"&&B[i].id.indexOf(C)==0){B[i].checked=A}}}function ClearContents(A){if(A){A.innerHTML=""}}function CompletePreferenceSet(B){var A=document.getElementById(B);if(A){A.className="PreferenceComplete"}}function Explode(B,A){return B.split(A)}function Focus(A){var B=document.getElementById(A);if(B){B.focus()}}function GetElements(A,B){var D=document.getElementsByTagName(A);var C=new Array();for(i=0;i<D.length;i++){if(D[i].id.indexOf(B)==0){C[C.length]=D[i]}}return C}function HideElement(A,C){var B=document.getElementById(A);if(B){B.style.display="none";if(C==1){ClearContents(B)}}}function PathFinder(){this.params=new function(){this.url=document.URL;this.domain=document.domain;this.httpMethod=this.url.replace(/^(http|https)(:\/\/).*$/,"$1$2");return this};this.getRootPath=function(C,B,F){var A=document.getElementsByTagName(C);var G="";var D="";for(var E=0;E<A.length;E++){G="";if(A[E].getAttribute&&A[E].getAttribute(B)){G=A[E].getAttribute(B)}else{if(A[E][B]){G=A[E][B]}}if(G.match(F)){D=G.replace(F,"");D=D.replace(/^http(s)?:\/\/[^\/]+/,"");break}}return D||false};return this}function PopTermsOfService(A){window.open(A,"TermsOfService","toolbar=no,status=yes,location=no,menubar=no,resizable=yes,height=600,width=400,scrollbars=yes")}function PreferenceSet(A){setTimeout("CompletePreferenceSet('"+this.Param+"');",400)}function RefreshPage(A){if(!A){A=400}setTimeout("document.location.reload();",A)}function RefreshPageWhenAjaxComplete(A){RefreshPage()}function SubmitForm(C,A,B){Wait(A,B);document[C].submit()}function SwitchElementClass(C,F,A,G,E,D){var B=document.getElementById(C);Sender=document.getElementById(F);if(B&&Sender){if(B.className==G){B.className=A;Sender.innerHTML=E}else{B.className=G;Sender.innerHTML=D}}}function SwitchExtension(E,F,D){var C=document.getElementById(F);if(C){C.className="Processing"}var B="ExtensionKey="+F+"&PostBackKey="+D;var A=new DataManager();A.Param=F;A.RequestFailedEvent=SwitchExtensionResult;A.RequestCompleteEvent=SwitchExtensionResult;A.LoadData(E+"?"+B)}function SwitchExtensionResult(B){var A=document.getElementById(Trim(B.responseText));if(A){setTimeout("SwitchExtensionItemClass('"+Trim(B.responseText)+"')",400)}else{alert(Trim(B.responseText))}}function SwitchExtensionItemClass(C){var B=document.getElementById(C);var A=document.getElementById("chk"+C+"ID");if(B&&A){B.className=A.checked?"Enabled":"Disabled"}}function SwitchPreference(G,F,C,E){var A=document.getElementById(F);var D=document.getElementById(F+"ID");if(D&&A){A.className="PreferenceProgress";var B=new DataManager();B.Param=F;B.RequestFailedEvent=HandleFailure;if(C==1){B.RequestCompleteEvent=RefreshPageWhenAjaxComplete}else{B.RequestCompleteEvent=PreferenceSet}B.LoadData(G+"?Type="+F+"&PostBackKey="+E+"&Switch="+D.checked)}}function Trim(A){return A.replace(/^\s*|\s*$/g,"")}function UpdateCheck(D,B,C){var A=new DataManager();A.RequestCompleteEvent=UpdateCheckStatus;A.RequestFailedEvent=UpdateCheckStatus;A.Param=D;A.LoadData(D+"?RequestName="+B+"&PostBackKey="+C)}function UpdateCheckStatus(E){if(E.responseText=="COMPLETE"){return }var F=E.responseText.substring(0,E.responseText.indexOf("|"));if(F=="First"){var B=document.getElementById("Core");var G=document.getElementById("CoreDetails")}else{var B=document.getElementById(F);var G=document.getElementById(F+"Details")}var D=E.responseText.slice(E.responseText.indexOf("|")+1);var A=document.getElementById("FormPostBackKey");var C=(A)?A.value:"";if(B&&G){if(D.indexOf("ERROR]")==1){B.className="UpdateError";G.innerHTML=D.replace(/\[ERROR\]/g,"")}else{if(D.indexOf("OLD]")==1){B.className="UpdateOld"}else{if(D.indexOf("UNKNOWN]")==1){B.className="UpdateUnknown"}else{B.className="UpdateGood"}}G.innerHTML=D.replace(/\[OLD\]/g,"").replace(/\[UNKNOWN\]/g,"").replace(/\[GOOD\]/g,"");setTimeout("UpdateCheck('"+this.Param+"', '"+F+"', '"+C+"');",300)}}else{alert("Error: "+E.responseText)}}function Wait(A,C){A.disabled=true;A.value=C;var B=A.parentNode;while(B!=null){if(B.tagName=="FORM"){B.submit();break}B=B.parentNode}}function WriteEmail(D,B,G,A){if(document.createElement&&document.getElementById){var E,C,F;E=document.createElement("a");E.href="mailto:"+B+"@"+D;G=G||B+"@"+D;F=document.createTextNode(G);E.appendChild(F);C=document.getElementById(A);if(C){C.parentNode.appendChild(E)}}}function showById(A){document.getElementById(A).style.display="block"};