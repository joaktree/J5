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
console.log('connecté'); 
// Je sélectionne l'image grand format
//const fullImg = document.getElementById('full'); 
//console.log(fullImg); 

// Je sélectionne les vignettes
const btn_alpha = document.querySelectorAll('.jt-content-accordion'); 
console.log(btn_alpha); 

// Je sélectionne le bouton
//const btn = document.querySelector('.btn-add'); 
//console.log(btn); 

// J'initialise le panier
//let panier = 0; 

// Je sélectionne la DIV panier-container
//const panierContainer = document.querySelector('.panier-container'); 

btn_alpha.forEach((item)=>{
    console.log('ce message apparaît pour chaque item du tableau');
 
    item.addEventListener('click', function(){
        // console.log(item, 'vignette cliquée'); 
        let btnSource = item.getAttribute('btn_a_index'); 
        console.log(btnSource); 
        // J'attribue la nouvelle url à l'image grand format
        //fullImg.setAttribute('src', imgSource);
    }); 
}); // fermeture de la forEach

function handleEvent(event) {
  // Create a new custom event object
  event = new CustomEvent('DOMEvent', {
    bubbles: true,
    cancelable: true,
    detail: {
      originalEvent: event,
      window: window
    }
  });

  // Call the event handler function 'i' in the context of 'j'
  if (i.call(j, event) === false) {
    // If the handler returns false, prevent default and stop propagation
    event.preventDefault();
    event.stopPropagation();
  }
}