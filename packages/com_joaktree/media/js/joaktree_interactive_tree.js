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
document.addEventListener('DOMContentLoaded', function() {
	
	if (typeof Joomla === 'undefined' || typeof Joomla.getOptions === 'undefined') {
		console.error('Joomla.getOptions not found!\nThe Joomla core.js file is not being loaded.');
		return false;
	}
	options_graph = Joomla.getOptions('joaktree_interactive_tree');
	if (typeof options_graph === 'undefined' ) {return false}

    let logs = [];

    const f3Chart = f3.createChart('#FamilyChart', data)
			    	.setTransitionTime(1000)
   				 	.setCardXSpacing(230)
    				.setCardYSpacing(150)
    				.setAncestryDepth(parseInt(options_graph.ancestors))
   					.setProgenyDepth(parseInt(options_graph.descendants))
					.setSingleParentEmptyCard(false)
                    .setShowSiblingsOfMain(false) // show brothers/sisters on main
 
    const f3Card = f3Chart.setCardHtml()
        .setMiniTree(true)
	    .setCardInnerHtmlCreator(d => {
			url = '',birth = ''
			if (d.data.data["birthday"]) birth = "<br>"+d.data.data["birthday"];
      return `<div class="card-inner" style="width: 200px; height: auto; padding: 15px; border-radius: 5px; text-align: center;">
        <div>${d.data.data["fullname"]}${birth}${url}</div>
      </div>`
        })
        .setOnCardUpdate(function(d) { // info box
            d3.select(this).select('.card').style('cursor', 'default')
            const card = this.querySelector('.card-inner')
            d3.select(card)
            .append('div')
            .attr('class', 'f3-svg-circle-hover')
            .attr('style', 'cursor: pointer; width: 20px; height: 20px;position: absolute; top: 0; right: 0;')
            .html(f3.icons.userSvgIcon())
            .select('svg')
            .style('padding', '0')
            .on('click', (e) => {
                e.stopPropagation()
                f3EditTree.open(d.data)
            })
        })
        f3Card.setOnCardClick(function (e) {
            // if latest selected items, store click information
            if (options_graph.latest == 'true') { 
                element = { id:e.currentTarget.getAttribute('data-id'),
                        fullname:e.currentTarget.parentNode.__data__.data.data.fullname
                        }
                // if latest selected items, store click information
                logs.push(element)
                if (logs.length > parseInt(options_graph.latestsize)) {
                    logs.shift()
                }
                updateLogDropdown(logs)
            }
            let info = document.querySelector('.f3-form-cont');
            if (info) { // info box opened ?
               info.classList.remove('opened');
            }
            f3Chart.updateMainId(e.currentTarget.getAttribute('data-id'))
    	    f3Chart.updateTree({initial: false})
        })
    // ----------- user details
    const f3EditTree = f3Chart.editTree()
        .setFields([{type:"text",label:options_graph.nametext,id:"fullname"},
                    {type:"text",label:options_graph.birthtext,id:"birthday"},
                    {type:"text",label:options_graph.deathtext,id:"deathday"},
                    {type:"text",label:"",id:"url"},
                    ]) 
        .setNoEdit()  //just see info form
        
	f3Chart.updateTree({initial: true})

    // with person_id this function will update the tree
	function updateTreeWithNewMainPerson(person_id, animation_initial = true) {
    	f3Chart.updateMainId(person_id)
    	f3Chart.updateTree({initial: animation_initial})
  	}
    if (options_graph.latest == 'true') {
    //---------- log selected boxes -----------
    let prevsbtn = d3.select(document.querySelector("#FamilyChart")).append("button").text(options_graph.latesttext)
    .attr('id','logprevsbtn')
    .attr("style", "position: absolute; top: 10px; right: 20px; width: 150px;display:none")
    .attr('data-bs-toggle','collapse')
    .attr('data-bs-target','#logprevs')
    .on("focusout", () => {
        setTimeout( () => {
                        log_cont.attr('class','collapse')
                    }, 200);
    })
    const log_cont = d3.select(document.querySelector("#FamilyChart")).append("div")
    .attr("style", "position: absolute; top: 40px; right: 20px; width: 150px; z-index:3")
    .attr('class','collapse')
    .attr('id','logprevs')

    function updateLogDropdown(options) {
        prevsbtn.attr("style", "position: absolute; top: 10px; right: 20px; width: 150px;z-index:3;display:block")
        options.reverse() // inverse le tableau
        dropdownlog.selectAll("div").data(options).join("div")
        .attr("style", "padding: 5px;cursor: pointer;border-bottom: .5px solid currentColor;")
        .on("click", (e, d) => {
            updateTreeWithNewMainPerson(d.id, true)
        })
        .text(d => d.fullname)
        options.reverse() //remet le tableau dans l'ordre
    }	
    const dropdownlog = log_cont.append("div").attr("style", "overflow-y: auto; max-height: 300px; background-color: options_graph.background;z-index:3")
    .attr("tabindex", "0")
    .on("wheel", (e) => {
      e.stopPropagation()
    })
    } // options_graph.latest = true
    if (options_graph.search == 'true') { 
  //------------ setup search dropdown -----------
    const all_select_options = []
    data.forEach(d => {
    if (all_select_options.find(d0 => d0.value === d["id"])) return
    all_select_options.push({label: `${d.data["fullname"]}`, value: d["id"]})
    })
    const search_cont = d3.select(document.querySelector("#FamilyChart")).append("div")
    .attr("style", "position: absolute; top: 10px; left: 10px; width: 150px; z-index: 1000;")
    .on("focusout", () => {
      setTimeout(() => {
        if (!search_cont.node().contains(document.activeElement)) {
          updateDropdown([]);
        }
      }, 200);
    })
    const search_input = search_cont.append("input")
        .attr("style", "width: 100%;")
        .attr("type", "text")
        .attr("placeholder", options_graph.searchtext)
        .on("focus", activateDropdown)
        .on("input", activateDropdown)

    const dropdown = search_cont.append("div").attr("style", "overflow-y: auto; max-height: 300px; background-color: options_graph.background")
        .attr("tabindex", "0")
        .on("wheel", (e) => {
        e.stopPropagation()
    })

    function activateDropdown() {
        const search_input_value = search_input.property("value")
        const filtered_options = all_select_options.filter(d => d.label.toLowerCase().includes(search_input_value.toLowerCase()))
        updateDropdown(filtered_options)
    }

    function updateDropdown(filtered_options) {
        dropdown.selectAll("div").data(filtered_options).join("div")
        .attr("style", "padding: 5px;cursor: pointer;border-bottom: .5px solid currentColor;")
        .on("click", (e, d) => {
            if (options_graph.latest == 'true') { // store in latest selected items list
                element = { id:d.value,fullname:d.label}
                logs.push(element)
                if (logs.length > parseInt(options_graph.latestsize)) { // keep 5 latest clicks 
                    logs.shift()
                }
                updateLogDropdown(logs)
            } // options_graph.latest = true
            let info = document.querySelector('.f3-form-cont'); // info box
            if (info) {
               info.classList.remove('opened');
            }
            updateTreeWithNewMainPerson(d.value, true)
         })
        .text(d => d.label)
    }	
    }// options_graph.search = true
}) // end of ready --------------
