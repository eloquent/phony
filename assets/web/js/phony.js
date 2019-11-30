var run = function () {
    var fetchVersions = function () {
        var request = new XMLHttpRequest();

        request.onerror = function (e) {
            console.error('Unable to load version data: ' + e.message);
        };

        request.onload = function () {
            if (request.status < 200 || request.status > 499) {
                console.error(
                    'Unable to load version data: ' +
                    request.statusText + ' (' + request.status + ')'
                );

                return;
            }

            var pageMatch = window.location.pathname.match(/\/([^/]+)$/)
            var page = pageMatch ? pageMatch[1] : ''

            var versions = JSON.parse(request.responseText);
            var currentVersion = document.body.getAttribute('data-version');
            var versionList = document.getElementById('versions');
            var isLatest = window.location.pathname.match(/\/latest\/$/);

            var latestItem = document.createElement('li');
            var latest = document.createElement('a');
            latest.textContent = 'latest (' + versions[0] + ')';
            latest.setAttribute(
                'href',
                '../latest/' + page + window.location.hash
            );

            if (isLatest) {
                latest.setAttribute('class', 'current');
            }

            latestItem.appendChild(latest);
            versionList.appendChild(latestItem);

            for (var i = 0; i < versions.length; ++i) {
                var versionItem = document.createElement('li');
                var version = document.createElement('a');
                version.textContent = versions[i];
                version.setAttribute(
                    'href',
                    '../' + encodeURIComponent(versions[i]) +
                        '/' + page + window.location.hash
                );

                if (!isLatest && versions[i] === currentVersion) {
                    version.setAttribute('class', 'current');
                }

                versionItem.appendChild(version);
                versionList.appendChild(versionItem);
            }
        };

        request.open('GET', '../data/versions.json', true);
        request.send();
    };

    var contentElement = document.getElementById('content');
    var tocElement = document.getElementById('toc');
    var tocScrollElement = document.getElementById('toc-scroll');
    var tocListElement = contentElement.querySelector('ul');
    var tocListElementCopy = tocListElement.cloneNode(true);

    tocListElement.style.display = 'none';
    tocScrollElement.appendChild(tocListElementCopy);

    tocScrollElement.addEventListener('wheel', function (event) {
        var tocHeight = tocScrollElement.clientHeight;
        var tocScrollHeight = tocScrollElement.scrollHeight;

        if (tocScrollHeight <= tocHeight) {
            return;
        }

        var scrollBottom = tocScrollHeight - tocHeight;
        var newScrollTop = tocScrollElement.scrollTop + event.deltaY;
        var shouldSuppress = false;

        if (newScrollTop >= scrollBottom) {
            newScrollTop = scrollBottom;
            shouldSuppress = true;
        } else if (newScrollTop < 1) {
            newScrollTop = 0;
            shouldSuppress = true;
        }

        if (shouldSuppress) {
            tocScrollElement.scrollTop = newScrollTop;

            event.stopPropagation();
            event.preventDefault();
            event.returnValue = false;

            return false;
        }

        return true;
    });

    var mainHeading = document.querySelector('h1')
    var mainHeadingAnchor = mainHeading.querySelector('a')

    var mainHeadingLink = document.createElement('a');
    mainHeadingLink.href = mainHeadingAnchor.hash;
    mainHeadingLink.appendChild(document.createTextNode(mainHeading.innerText));

    var mainHeadingListItem = document.createElement('li');
    mainHeadingListItem.style.display = 'none';
    mainHeadingListItem.appendChild(mainHeadingLink);

    tocListElementCopy.insertBefore(
        mainHeadingListItem,
        tocListElementCopy.querySelector('li')
    );

    var activateTocHeading = function (event) {
        if (!event) {
            tocScrollElement.scrollTop = 0;

            return;
        }

        var end = event.target;
        var tocHeight = tocScrollElement.clientHeight;
        var endOffset = end.offsetTop;

        if (tocScrollElement.scrollHeight > tocHeight) {
            tocScrollElement.scrollTop = endOffset - Math.floor(tocHeight / 2);
        }
    };

    var redrawToc = function () {
        tocElement.style.marginLeft = (870 - document.body.scrollLeft) + 'px';
    };

    var tocShowElement = document.getElementById('toc-show');
    var tocHideElement = document.getElementById('toc-hide');

    var showToc = function () {
        tocShowElement.style.display = 'none';
        tocHideElement.style.display = 'inline';
        tocListElement.style.display = 'block';
    };

    var hideToc = function () {
        tocHideElement.style.display = 'none';
        tocShowElement.style.display = 'inline';
        tocListElement.style.display = 'none';
    };

    var documentTitle = mainHeading.innerText;

    var dispatch = function (event) {
        var versionLinks = document.querySelectorAll('#versions a');

        for (var i = 0; i < versionLinks.length; ++i) {
            versionLinks[i].setAttribute(
                'href',
                versionLinks[i].pathname + window.location.hash
            );
        }

        if (window.location.hash) {
            hash = decodeURIComponent(window.location.hash.substring(1));

            var target;

            try {
                target = document.querySelector('#' + hash);
            } catch (e) {
                // not a standard anchor link
            }

            if (target && target.classList.contains('anchor')) {
                document.title =
                    target.parentNode.innerText + ' - ' + documentTitle;
            }

            target = null;

            try {
                target = document.querySelector('a[name="' + hash + '"]');
            } catch (e) {
                // not a standard anchor link
            }

            if (target) {
                var matches = hash.match(/^(\w+)\.(\w+)$/);

                if (matches) {
                    if ('facade' === matches[1]) {
                        document.title = matches[2] + '() - ' + documentTitle;
                    } else {
                        document.title =
                            '$' + matches[1] +
                            '->' + matches[2] +
                            '() - ' + documentTitle;
                    }
                }

                var matches = hash.match(/^(\w+)\.(\w+)\.(\w+)$/);

                if (matches) {
                    document.title =
                        '$' + matches[1] +
                        ' ' + matches[2] +
                        ' ' + matches[3] +
                        ' - ' + documentTitle;
                }
            }
        } else {
            document.title = documentTitle;
        }

        if ('#toc' === window.location.hash) {
            if (event) {
                event.preventDefault();
            }

            showToc();
            tocListElement.scrollIntoView();
        }
    };

    var upgradeSvg = function () {
        var images = document.querySelectorAll('img[src$=".svg"]');

        for (var i = 0; i < images.length; ++i) {
            var image = images[i];
            var link = image.parentNode;
            var container = link.parentNode;

            container.appendChild(image);
            container.removeChild(link);
        }

        SVGInjector(
            images,
            {},
            function () {
                hash = window.location.hash;
                window.location.hash = '#';
                window.location.hash = hash;
            }
        );
    };

    window.addEventListener('hashchange', dispatch);
    document.addEventListener('scroll', function () {
        requestAnimationFrame(redrawToc)
    });
    document.addEventListener('resize', function () {
        requestAnimationFrame(activateTocHeading)
    });
    tocHideElement.addEventListener('click', hideToc);

    new Gumshoe(
        '#toc-scroll > ul a',
        {
            nested: true,
            offset: 30,
            reflow: true,
        }
    );

    document.addEventListener('gumshoeActivate', activateTocHeading)

    fetchVersions();
    dispatch();
    upgradeSvg();
    redrawToc();
};

if (document.readyState != 'loading'){
    run();
} else {
    document.addEventListener('DOMContentLoaded', run);
}
