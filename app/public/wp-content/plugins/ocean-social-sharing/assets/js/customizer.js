/**
 * Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

var api   = wp.customize,
	style = [
		'minimal',
		'colored',
		'dark'
	],
	headerPosition = [
		'side',
		'top'
	];

api('oss_social_share_name', function(value) {
    value.bind(function(newVal) {
        var socialWraps = document.querySelectorAll('.entry-share');
        if (socialWraps.length) {
            socialWraps.forEach(function(element) {
                if (newVal === true) {
                    element.classList.add('has-name');
                } else {
                    element.classList.remove('has-name');
                }
            });
        }
    });
});

api('oss_social_share_heading', function(value) {
    var headings = document.querySelectorAll('.social-share-title span.text');
    if (headings.length) {
        var originalHeadings = Array.from(headings).map(function(heading) {
            return heading.innerHTML;
        });

        value.bind(function(newval) {
            headings.forEach(function(heading, index) {
                if (newval) {
                    heading.innerHTML = newval;
                } else {
                    heading.innerHTML = originalHeadings[index];
                }
            });
        });
    }
});

api('oss_social_share_heading_position', function(value) {
    value.bind(function(newval) {
        var socialWraps = document.querySelectorAll('.entry-share');
        if (socialWraps.length) {
            socialWraps.forEach(function(socialWrap) {
                headerPosition.forEach(function(position) {
                    socialWrap.classList.remove(position);
                });
                socialWrap.classList.add(newval);
            });
        }
    });
});

api('oss_social_share_style', function(value) {
    value.bind(function(newval) {
        var socialWraps = document.querySelectorAll('.entry-share');
        if (socialWraps.length) {
            socialWraps.forEach(function(socialWrap) {
                style.forEach(function(styleClass) {
                    socialWrap.classList.remove(styleClass);
                });
                socialWrap.classList.add(newval);
            });
        }
    });
});
