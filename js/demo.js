$(document).ready(function() {
    $('#header li').click(function() {
        $(this).parent().children().removeClass('selected');
        $(this).addClass('selected');
    });

    $('#header div.button').click(function() {
        var link = $(this).data('link');
        if (link) {
            window.location = link;
        }
    });

    $('#theme').change(function() {
        $('link:last').attr('href', 'css/timeline_' + $(this).val() + '.css?key=' + (new Date()).getTime());
    });

    $('#demo_type').change(function() {
        changeDemo(parseInt($(this).val(), 10));
    });

    $('#hashtag').keyup(function(e) {
        if (e.which === 13) {
            if ($.trim($('#hashtag').val()).replace(/#/, '') !== '') {
                twitterSearch();
            }
        }
    });

    $('#facebook_search').keyup(function(e) {
        if (e.which === 13) {
            if ($.trim($('#facebook_search').val()) !== '') {
                facebookSearch();
            }
        }
    });
});


function changeDemo(type, is_mobile) {
    $('#timeline').remove();
    var wrapper = $('<div>').attr('id', 'timeline').appendTo($(document.body));

    if (is_mobile) {
        wrapper.addClass('mobile');
    }

    var timeline_data = [];
    var options       = {};

    $('#timeline').addClass('demo' + type);

    switch (type) {
        case 1:
            timeline_data = [
                {
                    type:     'iframe',
                    date:     '2017-08-12',
                    title:    'Video',
                    width:    400,
                    height:   300,
                    url:      'https://www.youtube.com/embed/L8Z2bO-v8_o' // http://player.vimeo.com/video/30491762?byline=0&amp;portrait=0
                },
                {
                    type:     'slider',
                    date:     '2017-12-16',
                    width:    400,
                    height:   250,
                    images:   ['images/test.jpg', 'images/test1.jpg', 'images/test.jpg'],
                    speed:    5000
                },
                {
                    type:     'iframe',
                    date:     '2016-09-03',
                    title:    'Map',
                    width:    400,
                    height:   300,
                    url:      'https://maps.google.com.au/?ie=UTF8&amp;ll=-27.40739,153.002859&amp;spn=1.509276,2.515869&amp;t=v&amp;z=9&amp;output=embed'
                    //url:      'http://a.tiles.mapbox.com/v3/leli.map-s73ls1pc.html#14/-27.4718/153.02259999999998'
                },
                {
                    type:     'blog_post',
                    date:     '2016-08-03',
                    title:    'Blog Post',
                    width:    400,
                    content:  '<b>Lorem Ipsum</b> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.',
                    image:    'images/test.jpg',
                    readmore: 'http://www.manutd.com'
                },
                {
                    type:     'blog_post',
                    date:     '2010-08-03',
                    title:    'Blog Post',
                    width:    400,
                    content:  '<b>Lorem Ipsum</b> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.',
                    image:    'images/test.jpg',
                    readmore: 'http://www.scriptgates.com'
                },
                {
                    type:     'slider',
                    date:     '2010-12-16',
                    width:    400,
                    height:   200,
                    images:   ['images/test.jpg', 'images/test1.jpg'],
                    speed:    5000
                },

            ];
            options       = {
                animation:   true,
                lightbox:    true,
                showYear:    true,
                allowDelete: false,
                columnMode:  'dual'
            };
            break;
        case 2:
            timeline_data = [
                {
                    type:     'blog_post',
                    date:     '2011-09-03',
                    title:    'FA Cup',
                    width:    300,
                    content:  'The Reds go marching on in the FA Cup...',
                    image:    'images/facup.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2011-08-03',
                    title:    'Swansea',
                    width:    300,
                    content:  'Check out our exclusive video preview ahead of today\'s clash with Swansea <a href="http://bit.ly/Yz0bmZ" target="_blank">http://bit.ly</a>',
                    image:    'images/rio.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2011-07-15',
                    title:    'Manchester United VS Liverpool',
                    width:    300,
                    content:  'The Reds complete the double over Liverpool this season...',
                    image:    'images/evra.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2011-06-29',
                    title:    'Michael Carrick',
                    width:    300,
                    content:  'Last chance to win Michael Carrick\'s signed shirt from the Liverpool game!! Click this link to enter <a href="http://bit.ly/W03U8k" target="_blank">http://bit.ly</a>',
                    image:    'images/carric.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2011-04-02',
                    title:    'Match',
                    width:    300,
                    content:  '9 Premier League wins out of 10 this season at Old Trafford. What is your match of the season so far at the Theatre of Dreams?',
                    image:    'images/wigan.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2011-02-13',
                    title:    'Old Traffordt',
                    width:    300,
                    content:  'Check out our exclusive video preview ahead of today\'s clash with Swansea <a href="http://bit.ly/Yz0bmZ" target="_blank">http://bit.ly</a>',
                    image:    'images/home.jpg'
                }
            ];
            options       = {
                animation:   true,
                lightbox:    true,
                showYear:    false,
                allowDelete: false,
                columnMode:  'dual'
            };
            break;
        case 3:
            timeline_data = [
                {
                    type:     'slider',
                    date:     '2011-12-16',
                    width:    400,
                    height:   150,
                    images:   ['images/group.jpg', 'images/old.jpg', 'images/win.jpg'],
                    speed:    5000
                },
                {
                    type:     'gallery',
                    date:     '2011-04-12',
                    title:    'Mini Gallery',
                    width:    300,
                    height:   100,
                    images:   ['images/rooney.jpg', 'images/tshirt.jpg', 'images/giggs.jpg', 'images/rio.jpg', 'images/paper.jpg']
                },
                {
                    type:     'blog_post',
                    date:     '2011-08-03',
                    title:    'Blog Post',
                    width:    200,
                    content:  '<b>Lorem Ipsum</b> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.',
                    image:    'images/rio.jpg',
                    readmore: 'http://www.manutd.com'
                },
                {
                    type:     'slider',
                    date:     '2010-12-16',
                    width:    400,
                    height:   200,
                    images:   ['images/ferguson.jpg', 'images/paper.jpg'],
                    speed:    5000
                },
                {
                    type:     'gallery',
                    date:     '2010-04-12',
                    title:    'Mini Gallery',
                    width:    200,
                    height:   150,
                    images:   ['images/stadium.jpg', 'images/rafel.jpg', 'images/logo.jpg', 'images/rvp.jpg']
                },
                {
                    type:     'blog_post',
                    date:     '2010-08-03',
                    title:    'Blog Post',
                    width:    400,
                    content:  '<b>Lorem Ipsum</b> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.',
                    image:    'images/evra.jpg',
                    readmore: 'http://www.scriptgates.com'
                }
            ];
            options       = {
                animation:   true,
                lightbox:    true,
                showYear:    false,
                allowDelete: false,
                columnMode:  'left'
            };
            break;
        case 4:
            timeline_data = [
                {
                    type:     'slider',
                    date:     '2011-12-16',
                    width:    400,
                    height:   150,
                    images:   ['images/group.jpg', 'images/old.jpg', 'images/win.jpg'],
                    speed:    5000
                },
                {
                    type:     'gallery',
                    date:     '2011-04-12',
                    title:    'Mini Gallery',
                    width:    300,
                    height:   100,
                    images:   ['images/rooney.jpg', 'images/tshirt.jpg', 'images/giggs.jpg', 'images/rio.jpg', 'images/paper.jpg']
                },
                {
                    type:     'blog_post',
                    date:     '2011-08-03',
                    title:    'Blog Post',
                    width:    200,
                    content:  '<b>Lorem Ipsum</b> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.',
                    image:    'images/rio.jpg',
                    readmore: 'http://www.scriptgates.com'
                },
                {
                    type:     'slider',
                    date:     '2010-12-16',
                    width:    400,
                    height:   200,
                    images:   ['images/ferguson.jpg', 'images/paper.jpg'],
                    speed:    5000
                },
                {
                    type:     'gallery',
                    date:     '2010-04-12',
                    title:    'Mini Gallery',
                    width:    200,
                    height:   150,
                    images:   ['images/stadium.jpg', 'images/rafel.jpg', 'images/logo.jpg', 'images/rvp.jpg']
                },
                {
                    type:     'blog_post',
                    date:     '2010-08-03',
                    title:    'Blog Post',
                    width:    400,
                    content:  '<b>Lorem Ipsum</b> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.',
                    image:    'images/evra.jpg',
                    readmore: 'http://www.manutd.com'
                }
            ];
            options       = {
                animation:   true,
                lightbox:    true,
                showYear:    false,
                allowDelete: false,
                columnMode:  'right'
            };
            break;
        case 5:
            timeline_data = [
                {
                    type:     'blog_post',
                    date:     '2012-09-03',
                    title:    'FA Cup',
                    width:    '95%',
                    content:  'The Reds go marching on in the FA Cup...',
                    image:    'images/facup.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2011-08-03',
                    title:    'Swansea',
                    width:    '95%',
                    content:  'Check out our exclusive video preview ahead of today\'s clash with Swansea <a href="http://bit.ly/Yz0bmZ" target="_blank">http://bit.ly/Yz0bmZ</a>',
                    image:    'images/rio.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2011-07-15',
                    title:    'Manchester United VS Liverpool',
                    width:    '95%',
                    content:  'The Reds complete the double over Liverpool this season...',
                    image:    'images/evra.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2011-06-29',
                    title:    'Michael Carrick',
                    width:    '95%',
                    content:  'Last chance to win Michael Carrick\'s signed shirt from the Liverpool game!! Click this link to enter <a href="http://bit.ly/W03U8k" target="_blank">http://bit.ly/W03U8k</a>',
                    image:    'images/carric.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2011-04-02',
                    title:    'Match',
                    width:    '95%',
                    content:  '9 Premier League wins out of 10 this season at Old Trafford. What is your match of the season so far at the Theatre of Dreams?',
                    image:    'images/wigan.jpg'
                },
                {
                    type:     'blog_post',
                    date:     '2010-02-13',
                    title:    'Old Traffordt',
                    width:    '95%',
                    content:  'Check out our exclusive video preview ahead of today\'s clash with Swansea <a href="http://bit.ly/Yz0bmZ" target="_blank">http://bit.ly/Yz0bmZ</a>',
                    image:    'images/home.jpg'
                }
            ];
            options       = {
                animation:   true,
                lightbox:    true,
                showYear:    true,
                allowDelete: false,
                columnMode:  'center'
            };
            break;
    }

    var timeline = new Timeline($('#timeline'), timeline_data);
    timeline.setOptions(options);
    timeline.display();
}



String.prototype.parseURL = function() {
    return this.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&~\?\/.=]+/g, function(url) {
        return url.link(url);
    });
};

String.prototype.parseHashtag = function() {
    return this.replace(/[#]+[A-Za-z0-9-_]+/g, function(t) {
        var tag = t.replace("#","%23")
        return t.link("http://search.twitter.com/search?q="+tag);
    });
};


function twitterSearch() {
    $('#timeline').remove();
    var wrapper = $('<div>').attr('id', 'timeline').appendTo($(document.body));

    var hash_tag = $.trim($('#hashtag').val()).replace(/#/, '');

    if (hash_tag === '') {
        hash_tag = 'Anonymous';
    }

    $('#social_search').show();

    $.ajax({
        dataType: 'json',
        url:      'https://search.twitter.com/search.json?q=' + hash_tag + '&include_entities=true&callback=?',
        data:     {},
        error:    function() {
            alert('no results found, please try another search keyword');
        },
        success:  function(data) {
            $('#social_search').hide();

            var timeline_data = [];

            if (!data.results.length) {
                alert('no results found, please try another search keyword');
                return;
            }

            $(data.results).each(function(index, tweet_data) {
                var months = [];
                months['Jan'] = '01'; months['Feb'] = '02'; months['Mar'] = '03';
                months['Apr'] = '04'; months['May'] = '05'; months['Jun'] = '06';
                months['Jul'] = '07'; months['Aug'] = '08'; months['Sep'] = '09';
                months['Oct'] = '10'; months['Nov'] = '11'; months['Dec'] = '12';

                var date = tweet_data.created_at.split(' ');
                var year  = date[3];
                var month = months[date[2]];
                var day   = date[1];

                timeline_data.push({
                    type:     'blog_post',
                    date:     year + '-' + month + '-' + day,
                    title:    '<a href="http://www.twitter.com/' + tweet_data.from_user + '" target="_blank" style="text-decoration:none;color:#AAAAAA;">' + tweet_data.from_user_name + '</a>',
                    width:    300,
                    content:  '<div><img class="twitter_profile" align="left" src="' + tweet_data.profile_image_url + '" /></div>' + tweet_data.text.parseURL().parseHashtag()
                });
            });


            var options       = {
                animation:   true,
                lightbox:    true,
                showYear:    true,
                allowDelete: false,
                columnMode:  'dual'
            };

            var timeline = new Timeline($('#timeline'), timeline_data);
            timeline.setOptions(options);
            timeline.display();
        }
    });
}

function facebookSearch() {
    $('#timeline').remove();
    var wrapper = $('<div>').attr('id', 'timeline').appendTo($(document.body));

    var search = $.trim($('#facebook_search').val());

    if (search === '') {
        search = 'Anonymous';
    }

    $('#social_search').show();

    FB.api('/search', {'q':search, 'type':'post'}, function(data) {
        $('#social_search').hide();

        var timeline_data = [];

        if (!data.data.length) {
            alert('no results found, please try another search keyword');
            return;
        }

        $(data.data).each(function(index, facebook_data) {
            if (facebook_data.message && facebook_data.from.id) {
                var date = facebook_data.updated_time.split('-');
                var year  = date[0];
                var month = date[1];
                var day   = date[2].substr(0, 2);

                timeline_data.push({
                    type:     'blog_post',
                    date:     year + '-' + month + '-' + day,
                    title:    facebook_data.from.name,
                    width:    400,
                    content:  '<div><img class="facebook_profile" align="left" src="https://graph.facebook.com/'+facebook_data.from.id+'/picture?type=square" /></div>' + (facebook_data.message ? facebook_data.message.substr(0, 300).parseURL() : '')
                });
            }
        });


        var options       = {
            animation:   true,
            lightbox:    true,
            showYear:    true,
            allowDelete: false,
            columnMode:  'dual'
        };

        var timeline = new Timeline($('#timeline'), timeline_data);
        timeline.setOptions(options);
        timeline.display();
    });
}