YUI.add("moodle-availability_esse3enrols-form",function(i,e){M.availability_esse3enrols=M.availability_esse3enrols||{},M.availability_esse3enrols.form=i.Object(M.core_availability.plugin),M.availability_esse3enrols.form.initInner=function(){},M.availability_esse3enrols.form.getNode=function(e){var t,s="";return e.creating===undefined&&e.idnumbers!==undefined&&(s=e.idnumbers),e='<label class="form-group"><span class="p-r-1">'+M.util.get_string("title","availability_esse3enrols")+"</span>",e+='<span class="availability-esse3enrols">',""==s&&(e+='<input class="esse3enrols-file" name="esse3enrolsfile" type="file" />'),e=(e+='<input class="esse3enrols-list-field" name="idnumbers" type="hidden" value="'+s+'" />')+('<span class="esse3enrols-list">'+s.split(",").join("<br />")+"</span>"),s=i.Node.create('<span class="form-inline">'+(e+="</span></label>")+"</span>"),t=M.util.get_string("missing","availability_esse3enrols"),M.availability_esse3enrols.form.addedEvents||(M.availability_esse3enrols.form.addedEvents=!0,i.one(".availability-field").delegate("change",function(){var e,i,l,n,s;0<this._node.files.length&&(e=this._node.files[0],l=(i=this).next(".esse3enrolts-list-field"),n=this.next(".esse3enrols-list"),e&&((s=new FileReader).onload=function(e){var e=e.target.result,s=XLSX.read(e,{type:"binary"}),a=[];s.SheetNames.forEach(function(e){var e=XLSX.utils.sheet_to_row_object_array(s.Sheets[e]),i="matricola".toLowerCase();e[0].hasOwnProperty("GRUPPO_GIUD_COD")&&e.forEach(function(e){var s=parseInt(e.GRUPPO_GIUD_COD);e.hasOwnProperty("SUBSET")&&0<=s&&e.SUBSET.toLowerCase()!=i&&a.push(e.SUBSET)})}),0<a.length?(i.remove(),l.set("value",a.join(",")),n.set("innerHTML",a.join("<br />")),M.core_availability.form.update()):n.set("innerHTML",'<div class="invalid-feedback" style="display:block;">'+t+"</div>")},s.readAsBinaryString(e)))},".availability_esse3enrols input.esse3enrols-file")),s},M.availability_esse3enrols.form.focusAfterAdd=function(e){e.one("input:not([disabled])").focus()},M.availability_esse3enrols.form.fillValue=function(e,s){s=s.one("input[name=idnumbers]").get("value");e.idnumbers=""===s?"":s},M.availability_esse3enrols.form.fillErrors=function(e,s){""===s.one("input[name=idnumbers]").get("value")&&e.push("availability_esse3enrols:missing")}},"@VERSION@",{requires:["base","node","event","node-event-simulate","moodle-core_availability-form"]});