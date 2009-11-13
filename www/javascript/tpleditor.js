function setCaret(textObj){ 
   if(textObj.createTextRange){   
     textObj.caretPos=document.selection.createRange().duplicate();   
   } 
} 
function insertAtCaret(textObj,textFeildValue){ 
   if(document.all&&textObj.createTextRange&&textObj.caretPos){      
       var caretPos=textObj.caretPos;     
       caretPos.text=caretPos.text.charAt(caretPos.text.length-1)==''?textFeildValue+'':textFeildValue; 
   }else if(textObj.setSelectionRange){       
       var rangeStart=textObj.selectionStart; 
       var rangeEnd=textObj.selectionEnd;    
       var tempStr1=textObj.value.substring(0,rangeStart);     
       var tempStr2=textObj.value.substring(rangeEnd);     
       textObj.value=tempStr1+textFeildValue+tempStr2; 
       textObj.focus(); 
       var len=textFeildValue.length; 
       textObj.setSelectionRange(rangeStart+len,rangeStart+len); 
       textObj.blur(); 
   }else { 
     textObj.value+=textFeildValue; 
   } 
} 
