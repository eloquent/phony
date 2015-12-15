var run = function () {
    var contentElement = document.getElementById('content');
    var tocElement = document.getElementById('toc');
    var tocListElement = contentElement.querySelector('ul');
    var tocListElementCopy = tocListElement.cloneNode(true);

    tocListElement.style.display = 'none';
    tocElement.appendChild(tocListElementCopy);

    var phonyLink = document.createElement('a');
    phonyLink.href = '#phony';
    phonyLink.appendChild(document.createTextNode('Phony'));

    var phonyListItem = document.createElement('li');
    phonyListItem.style.display = 'none';
    phonyListItem.appendChild(phonyLink);

    tocListElementCopy.insertBefore(
        phonyListItem,
        tocListElementCopy.querySelector('li')
    );

    var activateTocHeading = function (data) {
        var activeElements = tocElement.querySelectorAll('.active');

        for (var i = 0; i < activeElements.length; ++i) {
            activeElements[i].classList.remove('active');
        }

        if (!data) {
            return;
        }

        var node = data.parent;
        node.classList.add('active');

        while (
            node.parentNode &&
            node.parentNode.parentNode &&
            'LI' == node.parentNode.parentNode.tagName
        ) {
            node = node.parentNode.parentNode;

            node.classList.add('active');
        }
    };

    var redrawToc = function () {
        tocElement.style.marginLeft = (870 - document.body.scrollLeft) + 'px';
    };

    gumshoe.init(
        {
            selector: '#toc > ul a',
            offset: 30,
            callback: activateTocHeading
        }
    );

    document.addEventListener('scroll', _.throttle(redrawToc, 10));
    redrawToc();

    var tocShow = document.getElementById('toc-show');
    var tocHide = document.getElementById('toc-hide');

    tocShow.addEventListener(
        'click',
        function (event) {
            tocShow.style.display = 'none';
            tocHide.style.display = 'inline';
            tocListElement.style.display = 'block';

            gumshoe.setDistances();
        }
    );
    tocHide.addEventListener(
        'click',
        function (event) {
            tocHide.style.display = 'none';
            tocShow.style.display = 'inline';
            tocListElement.style.display = 'none';

            gumshoe.setDistances();
        }
    );
};

document.addEventListener('DOMContentLoaded', run);
