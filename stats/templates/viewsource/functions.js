function confirmLink(theLink, msg)
{
    var is_confirmed=confirm(msg);
    if(is_confirmed) {
        theLink.href += '&is_js_confirmed=1';
    }

    return is_confirmed;
} // end of the 'confirmLink()' function


function setPointer(theRow, thePointerColor, theNormalBgColor)
{
    var theCells=null;
    if(thePointerColor == '' || typeof(theRow.style) == 'undefined') {
        return false;
    }
    if(typeof(document.getElementsByTagName) != 'undefined') {
        theCells=theRow.getElementsByTagName('td');
    }
    else if(typeof(theRow.cells) != 'undefined') {
        theCells=theRow.cells;
    }
    else {
        return false;
    }
    var rowCellsCnt =theCells.length;
    var currentColor=null;
    var newColor    =null;
    // Opera does not return valid values with "getAttribute"
    if(typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined' && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor=theCells[0].getAttribute('bgcolor');
        newColor    =(currentColor.toLowerCase() == thePointerColor.toLowerCase())
                     ? theNormalBgColor
                     : thePointerColor;
        for (var c=0; c < rowCellsCnt; c++) {
            theCells[c].setAttribute('bgcolor', newColor, 0);
        } // end for
    }
    else {
        currentColor=theCells[0].style.backgroundColor;
        newColor    =(currentColor.toLowerCase() == thePointerColor.toLowerCase())
                     ? theNormalBgColor
                     : thePointerColor;
        for (var c=0; c < rowCellsCnt; c++) {
            theCells[c].style.backgroundColor=newColor;
        }
    }

    return true;
} // end of the 'setPointer()' function