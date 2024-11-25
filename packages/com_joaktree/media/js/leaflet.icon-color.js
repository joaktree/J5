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
 * from Ilya Ilyankou <https://github.com/ilyankou>
*/

/*global L*/

(function (window, document, undefined) {
    "use strict";

    L.IconColor = {};

    L.IconColor.version = '1.1.0';

    L.IconColor.Icon = L.Icon.extend({
        options: {
            markerColor: 'white',
            iconColor: 'white',
            outlineColor: 'white',
            outlineWidth: '1',
            iconSize: [31, 42]
        },

        initialize: function (options) {
            options = L.Util.setOptions(this, options);
        },

        createIcon: function () {
            var options = L.Util.setOptions(this);
            var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            var path = document.createElementNS('http://www.w3.org/2000/svg', "path");
            var backgroundCircle = document.createElementNS('http://www.w3.org/2000/svg', "circle");
            var icongroup = document.createElementNS('http://www.w3.org/2000/svg', "g");
            var icon = document.createElementNS('http://www.w3.org/2000/svg', "text");

            svg.setAttribute('width', options.iconSize[0]);
            svg.setAttribute('height', options.iconSize[1]);
            svg.setAttribute('viewBox', '0 0 31 42');
            svg.setAttribute('class', 'l-icon-material');
            svg.setAttribute('style', 'margin-left:-' + parseInt(options.iconSize[0]/2) + 'px; margin-top:-' + options.iconSize[1] + 'px')
            svg.setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xlink", "http://www.w3.org/1999/xlink");
            
            backgroundCircle.setAttribute('cx', '15.5');
            backgroundCircle.setAttribute('cy', '15');
            backgroundCircle.setAttribute('r', '11');
            backgroundCircle.setAttribute('fill', options.markerColor);

            path.setAttributeNS(null, "d", "M15.6,1c-7.7,0-14,6.3-14,14c0,10.5,14,26,14,26s14-15.5,14-26C29.6,7.3,23.3,1,15.6,1z");
            path.setAttribute('fill', options.markerColor);
            path.setAttribute('stroke', options.outlineColor);
            path.setAttribute('stroke-width', options.outlineWidth);

            icon.textContent = options.icon;
            icon.setAttribute('x', '9');
            icon.setAttribute('y', '20');
            icon.classList.add('fa');
            icon.setAttribute('fill', options.iconColor);

            svg.appendChild(path);
            svg.appendChild(backgroundCircle);
            icongroup.appendChild(icon);

            svg.appendChild(icongroup);

            svg.setAttribute('transform', "matrix(1 0 0 1 -" + parseInt(options.iconSize[0] / 2) + " " + parseInt(options.iconSize[1]) + ")");

            return svg;
        }
    });
        
    L.IconColor.icon = function (options) {
        return new L.IconColor.Icon(options);
    };

}(this, document));