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
        gumshoe.setDistances();
    };

    var tocShowElement = document.getElementById('toc-show');
    var tocHideElement = document.getElementById('toc-hide');

    var showToc = function () {
        tocShowElement.style.display = 'none';
        tocHideElement.style.display = 'inline';
        tocListElement.style.display = 'block';

        gumshoe.setDistances();
    };

    var hideToc = function () {
        tocHideElement.style.display = 'none';
        tocShowElement.style.display = 'inline';
        tocListElement.style.display = 'none';

        gumshoe.setDistances();
    };

    var dispatch = function (event) {
        if (window.location.hash) {
            hash = decodeURIComponent(window.location.hash.substring(1));

            var target;

            try {
                target = document.querySelector('#' + hash);
            } catch (e) {
                // not a standard anchor link
            }

            if (target && target.classList.contains('anchor')) {
                document.title = target.parentNode.innerText + ' - Phony';
            }

            target = null;

            try {
                target = document.querySelector('a[name="' + hash + '"]');
            } catch (e) {
                // not a standard anchor link
            }

            if (target && hash.match(/^\w+\.\w+$/)) {
                document.title = hash + '() - Phony';
            }
        } else {
            document.title = 'Phony';
        }

        if ('#toc' === window.location.hash) {
            if (event) {
                event.preventDefault();
            }

            showToc();
            tocListElement.scrollIntoView();
        }
    };

    window.addEventListener('hashchange', dispatch);
    document.addEventListener('scroll', _.throttle(redrawToc, 10));
    tocHideElement.addEventListener('click', hideToc);

    gumshoe.init(
        {
            selector: '#toc > ul a',
            offset: 30,
            callback: activateTocHeading
        }
    );
    dispatch();
    redrawToc();
};

document.addEventListener('DOMContentLoaded', run);
