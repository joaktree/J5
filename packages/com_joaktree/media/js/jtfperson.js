/**
 * Joomla! component Joaktree
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2024)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * Joomla! 5.x conversion by Conseilgouz
 *
 */
 var jtfpoptions;
window.addEventListener('DOMContentLoaded',function(){
    
  	jtfpoptions = Joomla.getOptions('jtfperson');
	if (typeof jtfpoptions === 'undefined' ) {return false}
    setCheckValue(jtfpoptions);
})
function setCheckValue() {
    var link1 = jtfpoptions.link1;
    var link2 = jtfpoptions.link2;
    var link  = "";
    var search1 = document.getElementById("jform_person_firstName").value;
    if (jtfpoptions.patronym) {
        var search2 = document.getElementById("jform_person_patronym").value;
    }
    var search3 = document.getElementById("jform_person_rawFamilyName").value;
    if (jtfpoptions.patronym) {
        if ((search1 == "") && (search2 == "") && (search3 == "")) {
            link1 = "#";link2 = "#";
        } else {
            if (jtfpoptions.sef) { 
                if (search1 != "") { link = link + "/f-" + search1; } 
                if (search2 != "") { link = link + "/s-" + search2; } 
                if (search3 != "") { link = link + "/n-" + search3; } 
            } else {
                if (search1 != "") { link = link + "&amp;search1=" + search1;}
                if (search2 != "") { link = link + "&amp;search2=" + search2;}  
                if (search3 != "") { link = link + "&amp;search3=" + search3;} 
            }
        }
    } else { // no search2 (= patronym)
        if ((search1 == "") && (search3 == "")) {
            link1 = "#"; link2 = "#";
        } else {
            if (jtfpoptions.sef) { 
                if (search1 != "") { link = link + "/f-" + search1; } 
                if (search3 != "") { link = link + "/n-" + search3; } 
            } else {
                if (search1 != "") { link = link + "&amp;search1=" + search1; } 
                if (search3 != "") { link = link + "&amp;search3=" + search3; } 
            }
        }
    }
    if (link) {
        document.getElementById('iframeModalWindowcheck1').src = link1 + link;
        document.getElementById('iframeModalWindowsave1').src = link2 + link;
        document.getElementById('iframeModalWindowsave2').src = link2 + link;
    }
    //document.getElementById("check1").href = link1 + link;
    //document.getElementById("save1").href =  link2 + link;
    //document.getElementById("save2").href = link2 + link;
}

function jtNewPerson() {
    // SqueezeBox.close();
    document.getElementById("newstatus").value="checked";
    var el1 = document.getElementById('iframeModalWindowsave1');
    el1.src = jtfpoptions.urlclose;
    el1.setProperty("onclick", "jtsubmitbutton('save');");
    var el2 = document.getElementById('iframeModalWindowsave2');
    el2.src = jtfpoptions.urlclose;
    el2.setProperty("onclick", "jtsubmitbutton('save');");
}
// if (options.indParent1) {
function jtSelectPerson(appId, personId, relationId, familyId) {
    // SqueezeBox.close();
    var fam = new Element("option", {value: relationId + "!" + familyId}); 
    fam.inject(document.getElementById("jform_person_relations_family"));
    document.getElementById("jform_person_relations_family").value = relationId + "!" + familyId;
    document.getElementById("jform_person_id").value = personId;
    document.getElementById("jform_person_status").value = "relation";
    jtsubmitbutton("select");
}

function jtSelectPerson(appId, personId) {
    // SqueezeBox.close();
    document.getElementById("jform_person_id").value = personId;
    document.getElementById("jform_person_status").value = "relation";
    jtsubmitbutton("select");
}

function jtSavePerson() {
    if (document.getElementById("save1").style.display == 'block') { // still opened : close it
        document.getElementById("save1").close(); // SqueezeBox.close();
    }
    jtsubmitbutton("save");
}
    