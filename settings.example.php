<?php
$CONFIG = Minds\Core\Di\Di::_()->get('Config');

$CONFIG->minds_debug = true;

/*
 * Cassandra configuration
 */
$CONFIG->cassandra = (object) [
    'keyspace'    => '{{cassandra-keyspace}}',
    'servers'     => [ '{{cassandra-server}}' ],
    'cql_servers' => [ '{{cassandra-server}}' ],
    'username' => 'cassandra',
    'password' => 'cassandra',
];

$CONFIG->redis = [
    'master' => 'redis',
    'slave' => 'redis'
];

$CONFIG->rabbitmq = [
    'host' => 'rabbitmq',
    'port' => 5672,
    'username' =>  'guest',
    'password' =>  'guest'
];

$CONFIG->disable_secure_cookies = true;

$CONFIG->set('sessions', [
    'private_key' => 'file:///.dev/minds.pem',
    'public_key' => 'file:///.dev/minds.pub',
]);

$CONFIG->set('oauth', [
    'clients' => [
        'mobile' => [
            'secret' => '',
        ],
    ],
    'encryption_key' => '{{ jwt-secret }}',
 ]);

$CONFIG->set(
    'report_reasons',
    [
    [
      'value' => 1,
      'label' => 'Illegal',
      'hasMore' => true,
      'reasons' => [
        [ 'value' => 1, 'label' => 'Terrorism' ],
        [ 'value' => 2, 'label' => 'Paedophilia' ],
        [ 'value' => 3, 'label' => 'Extortion' ],
        [ 'value' => 4, 'label' => 'Fraud' ],
        [ 'value' => 5, 'label' => 'Revenge Porn' ],
        [ 'value' => 6, 'label' => 'Sex trafficking' ],
      ],
    ],
    [
      'value' => 2,
      'label' => 'NSFW (not safe for work)',
      'hasMore' => true,
      'reasons' => [ // Explicit reasons
        [ 'value' => 1, 'label' => 'Nudity' ],
        [ 'value' => 2, 'label' => 'Pornography' ],
        [ 'value' => 3, 'label' => 'Profanity' ],
        [ 'value' => 4, 'label' => 'Violance and Gore' ],
        [ 'value' => 5, 'label' => 'Race, Religion, Gender' ],
      ],
    ],
    [
      'value' => 3,
      'label' => 'Encourages or incites violence',
      'hasMore' => false,
    ],
    [
      'value' => 4,
      'label' => 'Harassment',
      'hasMore' => false,
    ],
    [
      'value' => 5,
      'label' => 'Personal and confidential information',
      'hasMore' => false,
    ],
    [
      'value' => 7,
      'label' => 'Impersonates',
      'hasMore' => false,
    ],
    [
      'value' => 8,
      'label' => 'Spam',
      'hasMore' => false,
    ],
    [
      'value' => 10,
      'label' => 'Infringes my copyright',
      'hasMore' => true,
    ],
    [
      'value' => 12,
      'label' => 'Incorrect use of hashtags',
      'hasMore' => false,
    ],
    [
      'value' => 13,
      'label' => 'Malware',
      'hasMore' => false,
    ],
    [
      'value' => 15,
      'label' => 'Trademark infringement',
      'hasMore' => false,
    ],
    [
      'value' => 16,
      'label' => 'Token manipulation',
      'hasMore' => false,
    ],
    [ 'value' => 11,
     'label' => 'Another reason',
     'hasMore' => true,
    ],
  ]
);


/**
 * Other Elgg Settings
 */
$CONFIG->installed = true;
$CONFIG->path = '{{path}}';
$CONFIG->plugins_path = '{{path}}plugins/';
$CONFIG->pluginspath = '{{path}}plugins/';
$CONFIG->dataroot = '{{dataroot}}';
$CONFIG->default_site = '{{default-site}}';
$CONFIG->site_id = '{{default-site}}';
$CONFIG->site_name = '{{site-name}}';
$CONFIG->__site_secret__ = '{{site-secret}}';
// $CONFIG->cdn_url = 'http://{{domain}}/';
$CONFIG->site_url = 'http://{{domain}}/';
$CONFIG->cdn_url = 'http://{{domain}}/';
$CONFIG->cdn_assets_url = 'http://{{domain}}/en/';
$CONFIG->zmq_server = 'localhost';
$CONFIG->checkout_url = 'http://{{checkout_domain}}/';

/**
 * Overrides default system cache path from inside data root to custom location.
 *
 * @global string $CONFIG->system_cache_path
 * @name $CONFIG->system_cache_path
 */
$CONFIG->system_cache_path = '{{cache-path}}';

/**
 * Elasticsearch Settings
 */
//server for elasticsearch
$CONFIG->elasticsearch_server = '{{elasticsearch-server}}';
//namespace
$CONFIG->elasticsearch_prefix = '{{elasticsearch-prefix}}';

$CONFIG->elasticsearch = [
    'hosts' => ['elasticsearch'],
    'index' => 'minds_badger',
    'metrics_index' => 'minds-metrics',
    'tags_index' => 'minds-trending-hashtags',
];

/**
 * Memcache setup (optional)
 * This is where you may optionally set up memcache.
 *
 * Requirements:
 * 	1) One or more memcache servers (http://www.danga.com/memcached/)
 *  2) PHP memcache wrapper (http://uk.php.net/manual/en/memcache.setup.php)
 *
 * Note: Multiple server support is only available on server 1.2.1
 * or higher with PECL library > 2.0.0
 */

 /*$CONFIG->memcache = true;

$CONFIG->memcache_servers = array (
    array('server1', 11211),
    array('server2', 11211)
);*/

/**
 * Queue Settings
 */
$CONFIG->queue = [
    'exchange' => '{{ queue-exchange }}'
];

/**
 * Use non-standard headers for broken MTAs.
 *
 * The default header EOL for headers is \r\n.  This causes problems
 * on some broken MTAs.  Setting this to TRUE will cause Elgg to use
 * \n, which will fix some problems sending email on broken MTAs.
 *
 * @global bool $CONFIG->broken_mta
 */
$CONFIG->broken_mta = false;

/**
 * Minimum password length
 *
 * This value is used when validating a user's password during registration.
 *
 * @global int $CONFIG->min_password_length
 */
$CONFIG->min_password_length = 6;

$CONFIG->set('plugins', [
  'Messenger',
  'oauth2',
  'guard',
]);

$CONFIG->set('sockets-jwt-secret', '{{jwt-secret}}');
$CONFIG->set('sockets-jwt-domain', '{{jwt-domain}}');
$CONFIG->set('sockets-server-uri', '{{socket-server-uri}}');

$CONFIG->set('facebook', [
    'app_id' => '{{facebook-app-id}}',
    'app_secret' => '{{facebook-app-secret}}'
]);

$CONFIG->set('twitter', [
    'api_key' => '{{twitter-app-id}}',
    'api_secret' => '{{twitter-app-id}}'
]);

$CONFIG->set('twilio', [
    'account_sid' => '{{twilio-account-sid}}',
    'auth_token' => '{{twilio-auth-token}}',
    'from' => '{{twilio-from}}'
]);

$CONFIG->set('google', [
    'geolocation' => '{{google-api-key}}',
    'translation' => '{{google-api-key}}',
    'push' => '{{google-api-key}}',
    'analytics' => [
        'service_account' => [
            'key_path' => __DIR__ . '/.auth/analytics.json',
        ],
        'ads' => '', // get it from https://ga-dev-tools.appspot.com/account-explorer/
    ]
]);

$CONFIG->set('apple', [
    'sandbox' => '{{apple-sandbox-enabled}}',
    'cert' => '{{apple-certificate}}'
]);

$CONFIG->set('boost', [
    'network' => [
        'min' => 100,
        'max' => 5000,
    ],
    'peer' => [
        'min' => 100,
        'max' => 5000000
    ],
]);

/* Maximum view per day */
$CONFIG->set('max_daily_boost_views', 10000);

$CONFIG->set('encryptionKeys', [
    'email' => [
        'private' => '{{email-private-key}}',
        'public' => '{{email-public-key}}'
    ],
    'phone_number' => [
        'private' => '{{phone-number-private-key}}',
        'public' => '{{phone-number-public-key}}'
    ]
]);

$CONFIG->set('payouts', [
    'initialDate' => '2016-11-01',
    'retentionDays' => 40,
    'minimumAmount' => 100,
    'userPercentage' => 0.8
 ]);

$CONFIG->set('payments', [
    'stripe' => [
        'api_key' => '',
        'transfers' => [
            'source_type' => 'bank_account'
        ]
    ]
]);

$CONFIG->set('sandbox', [
    'enabled' => false,
    'default' => [
        'guid' => '',
    ],
    'merchant' => [
        'guid' => '',
    ],
]);

$CONFIG->set('sns_secret', '{{sns-secret}}');

$CONFIG->set('blockchain', [
    'sale' => 'sale',
    'testnet' => false,

    'rpc_endpoints' => [
        'https://mainnet.infura.io/v3/708b51690a43476092936f9818f8c4fa',
    ],

    //'network_address' => 'https://rinkeby.infura.io/',
    'proxy_rpc_endpoint' => 'https://mainnet.infura.io/v3/708b51690a43476092936f9818f8c4fa',

    'client_network' => 1, // 1 = main ethereum network; 4 = test rinkeby; 1337 coin repo's testserver.sh

    'default_gas_price' => 40,
    'server_gas_price' => 40,
    'token_symbol' => 'status',

    'token_address' => '0xb26631c6dda06ad89b93c71400d25692de89c068',
    'contracts' => [
        'token_sale_event' => [
            'contract_address' => '0xf3c9dbb9598c21fe64a67d0586adb5d6eb66bc63',
            'wallet_address' => '0x1820fFAD63fD64d7077Da4355e9641dfFf4DAD0d',
            'wallet_pkey' => '',
            'eth_rate' => 2000, //1 ETH = 2,000 TOKENS
            'auto_issue_cap' => "120000000000000000000000", //60ETH (120,000 tokens) $30,000 USD
        ],
        'withdraw' => [
            'contract_address' => '0xdd10ccb3100980ecfdcbb1175033f0c8fa40548c',
            'wallet_address' => '0x14E421986C5ff2951979987Cdd82Fa3C0637D569',
            'wallet_pkey' => '',
            'limit_exemptions' => [
            ],
        ],
        'bonus' => [
            'wallet_address' => '0x461f1C5768cDB7E567A84E22b19db0eABa069BaD',
            'wallet_pkey' => '',
        ],
        'boost' => [
            'contract_address' => '0x112ca67c8e9a6ac65e1a2753613d37b89ab7436b',
            'wallet_address' => '0xdd04D9636F1944FE24f1b4E51Ba77a6CD23b6fE3',
            'wallet_pkey' => '',
        ],
        'wire' => [
            'plus_address' => '',
            'plus_guid' => '', // Your plus user's guid.
            'contract_address' => '0x4b637bba81d24657d4c6acc173275f3e11a8d5d7',
            'wallet_address' => '0x4CDc1C1fd1A3F4DD63231afF8c16501BcC11Df95',
            'wallet_pkey' => '',
        ],
     ],

    'eth_rate' => 2000, //1 ETH = 2,000 TOKENS

    'disable_creditcards' => true,

    'offchain' => [
        'cap' => 1000
    ],

    'mw3' => '/usr/bin/env node ' . __MINDS_ROOT__ . '/../mw3/index.js'
]);

$CONFIG->set('blockchain_override', [
    'pledge' => [
        // ...
    ],
]);

$CONFIG->set('plus', [
    'handler' => '',
    'tokens' => [
        'month' => 5,
        'year' => 50
    ]
]);

$CONFIG->set('iframely', [
    'key' => 'f4da1791510e9dd6ad63bc',
    'origin' => 'minds'
]);

$CONFIG->set('default_email_subscriptions', [
    [
        'campaign' => 'when',
        'topic' => 'unread_notifications',
        'value' => true
    ],
    [
        'campaign' => 'when',
        'topic' => 'wire_received',
        'value' => true
    ],
    [
        'campaign' => 'when',
        'topic' => 'boost_completed',
        'value' => true
    ],

    [
        'campaign' => 'with',
        'topic' => 'top_posts',
        'value' => 'periodically'
    ],
    [
        'campaign' => 'with',
        'topic' => 'channel_improvement_tips',
        'value' => true
    ],
    [
        'campaign' => 'with',
        'topic' => 'posts_missed_since_login',
        'value' => true
    ],
    [
        'campaign' => 'with',
        'topic' => 'new_channels',
        'value' => true
    ],

    [
        'campaign' => 'global',
        'topic' => 'minds_news',
        'value' => false
    ],
    [
        'campaign' => 'global',
        'topic' => 'minds_tips',
        'value' => true
    ],
    [
        'campaign' => 'global',
        'topic' => 'exclusive_promotions',
        'value' => false
    ],
]);


$CONFIG->set('i18n', [
    'languages' => [
        'en' => 'English',
        'es' => 'Español',
    ]
]);

// blacklist of internal IPs / URLs to block from curl requests
$CONFIG->set('internal_blacklist', []);

$CONFIG->set('tags', [
    'art', 'music', 'journalism', 'blockchain', 'freespeech', 'news', 'gaming', 'myphoto', 'nature', 'photography', 'politics', 'top', 'bitcoin', 'technology', 'food', 'animals', 'health', 'science', 'philosophy', 'comedy', 'film', 'minds'
]);

$CONFIG->set('steward_guid', '');
$CONFIG->set('steward_autoconfirm', false);
$CONFIG->set('development_mode', '{{development_mode}}');

$CONFIG->set('max_video_length', 900);

$CONFIG->set('max_video_length_plus', 1860);

$CONFIG->set('features', [
    'es-feeds' => false,
    'helpdesk' => true,
    'top-feeds' => true,
    'cassandra-notifications' => true,
    'dark-mode' => true,
    'allow-comments-toggle' => false,
    'permissions' => false,
    'pro' => false,
]);

$CONFIG->set('email', [
    'smtp' => [
        'host' => '',
        'username' => '',
        'password' => '',
        'port' => 465
    ]
]);

/* Maximum video length for non-plus users */
$CONFIG->set('max_video_length', 900);

/* Maximum video length for plus */
$CONFIG->set('max_video_length_plus', 1860);

/* Maximum video file size, in bytes */
$CONFIG->set('max_video_file_size', 3900000000);

$CONFIG->set('aws', [
    'key' => '',
    'secret' => '',
    'useRoles' => false,
    'bucket' => 'cinemr',
    'staticStorage' => 'cinemr_dev',
    'region' => 'us-east-1',
    'account_id' => '324044571751',
    'elastic_transcoder' => [
        'pipeline_id' => '1401290942976-efm3xj',
        'presets' => [
            "360.mp4" => "1351620000001-000040",
            "720.mp4" => "1351620000001-000010",
            "360.webm" => "1404848610623-0blc5v",
            "720.webm" => "1404852762051-zzvwfq"
        ],
        'dir' => 'cinemr_dev'
    ],
    'queue' => [
        'namespace' => 'EmiDev',
        'wait_seconds' => 3,
    ]
]);

$CONFIG->set('transcode', [
    //'free_threshold' => 900, // 15 minutes
    'free_threshold' => 2,
    'hd_price' => 1, // tokens
    'fhd_price' => 1.5,  // tokens
]);

$CONFIG->set('transcoder', [
    'threads' => 4,
    'dir' => 'cinemr_dev',
    'presets' => [
        [
            'width' => 640,
            'height' => 360,
            'bitrate' => 500,
            'audio_bitrate' => 80,
            'formats' => [ 'mp4', 'webm' ],
            'pro' => false,
        ],
        [
            'width' => 1280,
            'height' => 720,
            'bitrate' => 2000,
            'audio_bitrate' => 128,
            'formats' => [ 'mp4', 'webm' ],
            'pro' => false,
        ],
        [
            'width' => 1920,
            'height' => 1080,
            'bitrate' => 2000,
            'audio_bitrate' => 128,
            'formats' => [ 'mp4', 'webm' ],
            'pro' => true,
        ],
    ]
]);

$CONFIG->cinemr_url = 'https://cinemr.s3.amazonaws.com/cinemr_dev/';

$CONFIG->mongodb_servers = ['minds_mongo_1'];

$CONFIG->set('last_tos_update', 1);

$CONFIG->set('gitlab', [
    'project_id' => [
        'mobile' => '10171280', // project id mobile
        'front' => '10152778', // project id front
    ],
    'private_key' => ''
]);

$CONFIG->set('pro', [
    'handler' => '',
    'root_domains' => ['minds.com', 'www.minds.com', 'localhost'],
    'subdomain_suffix' => 'minds.com',
    'dynamodb_table_name' => 'traefik',
]);


$CONFIG->set('upgrades', [
    'pro' => [
        'monthly' => [
            'tokens' => 240,
            'usd' => 60,
        ],
        'yearly' => [
            'tokens' => 2400,
            'usd' => 600,
        ]
    ],
    'plus' => [
        'monthly' => [
            'tokens' => 28,
            'usd' => 7,
        ],
        'yearly' => [
            'tokens' => 240,
            'usd' => 60,
        ]
    ],
]);

$CONFIG->set('prohibited_domains', [
    'movieblog.tumblr.com',
    'moviehdstream.wordpress.com',
    'moviehq.tumblr.com',
    'moviehq.webs.com',
    'moviehq.wordpress.com',
    'movieo.wordpress.com',
    'movieonline.tumblr.com',
    'movieonline.webs.com',
    'movieonline.wordpress.com',
    'movieonlinehd.tumblr.com',
    'movieonlinehd.webs.com',
    'movieonlinehd.wordpress.com',
    'movies.tumblr.com',
    'moviesf.tumblr.com',
    'moviesgodetia.com',
    'movieslinks4u',
    'moviesmount.com',
    'moviesmonster.biz',
    'moviesondesktop',
    'moviesonlinefree.biz',
    'moviestream.wordpress.com',
    'movieontop.com',
    'afllivestreaming.com.au',
    'londonolympiccorner',
    'nrllivestreaming.com.au',
    '24x7livestreamtvchannels.com',
    'www.edogo.us',
    'all4health.in',
    'watches4a.co.uk',
    'es.jennyjoseph.com',
    'allsportslive24x7.blogspot.com',
    'boxing-tv-2014-live-stream.blogspot.com',
    'amarblogdalima.blogspot.com',
    'www.officialtvstream.com.es',
    'topsalor.com',
    'busybo.org',
    'www.nowvideo.sx',
    '180upload.com',
    'allmyvideos.net',
    'busybo.org',
    'hdmovieshouse.biz',
    'sportblog.info',
    'psport.space',
    'discus.space',
    'euro2016.it.ua',
    'neymar.space',
    'espnstream.space',
    '2016.vn.u',
    'blogstream.space',
    'liveextratime.xyz',
    'thebestlive.xyz',
    'streamoffside.xyz',
    'sportmaster2014.page.tl',
    'bloggersdelight.dk',
    'watchsportslive.space',
    'freeforward.xyz',
    'live4sports.xyz',
    'streamfun.xyz',
    'angelfire.com',
    'streamtime.xyz',
    'futebol2star.com',
    'live2sport.com',
    'newssports.space',
    'onlineolympics.xyz',
    'liveolympics.xyz',
    'streamontv.xyz',
    'londonschedule.com',
    'onlineolympics.space',
    'sportwinning.xyz',
    'streamworld.xyz',
    'streamtop.xyz',
    'livechampion.xyz',
    'playstreams.xyz',
    'live4sport.xyz',
    'streampage.xyz',
    'calendarsport.space',
    'fsport.space',
    'euro2016.od.ua',
    'streambig.xyz',
    'sportprediction.xyz',
    'streamwork.xyz',
    'r041.donnael.com',
    '2016.lt.ua',
    'vipleague.se',
    'liveonline.company',
    'liveolympics.space',
    'seoandvideomarketing.com.au',
    'vipbox.sx',
    'germanypolandlivestream.club',
    'sportgoal.xyz',
    'ggdbsale.com',
    'gorillasteroids.eu',
    'watchlivesports.space',
    'penaltyshootout.xyz',
    'streamgroup.xyz',
    'streamnew.xyz',
    'cottonsport.space',
    'gosport.space',
    'streambest.xyz',
    'penaltyspot.xyz',
    'streamthe.xyz',
    'liveevents.name',
    'londonblog.work',
    'testcollections.com',
    'alfagy.com',
    'teravide1974.full-design.com',
    'selfnarhasbllaq1980-blog.logdown.com',
    'neipononchoi1984.suomiblog.com',
    'gemttranlonthe1985.blogzet.com',
    'pitchero.com',
    'blogolize.com',
    'lisbopholsven1974.thezenweb.com',
    'blogocial.com',
    'tinyblogging.com',
    'share.pho.to',
    'community.vietfun.com',
    'ockuderla1985.full-design.com',
    'unmosimla1978.total-blog.com',
    'gemttranlonthe1985.blogzet.com',
    'rapptubizboe1978.blogminds.com',
    'descduclighgon1973.full-design.com',
    'ricphosati1972.full-design.com',
    'fuddbluslanmaa1975.blogdigy.com',
    'smarforcute1976.blogdigy.com',
    'xn--90aizihgi.xn--p1ai',
    'tinyurl.com',
    'bit.ly',
    'bit.do',
    '123football.space',
    'bitly.com',
    'j.mp',
    'livestreaming.one',
    'livestreaming.life',
    'forbest.pw',
    'olizev.tdska2ll.ru',
    'tdska2ll.ru',
    'tdska1ll.ru',
    'tdska3ll.ru',
    'tdska4ll.ru',
    'ihmail.ru',
    'tdska5ll.ru',
    'tdska6ll.ru',
    'll.ru',
    'shorl.com',
    'scorestream.space',
    'bestsplayer.xyz',
    'worldwideevents.space',
    'worldseries.space',
    'best247chemist.net',
    '9tn.ru',
    'futbolkin2013.ru',
    'playnowstore.com',
    'qr-url.tk',
    'watchonlinerugby.net',
    'esecuritys.com',
    'rufile.no-ip.ca',
    'imzonline.com',
    'femeedia.com',
    'mediomatic.com',
    'savemoneyeasily.com',
    'option1pro.com',
    'perron07.nl',
    'movieonrails.com',
    'topmoviestoday.com',
    'playnowstore.com',
    'g-files.biz',
    'dawnloadonline.com',
    'thedirsite.com',
    'siteslocate.com',
    'mydrugdir.com',
    'find24hs.com',
    'veeble.org',
    'movieonrails.com',
    'bestmoviehd.net',
    'putmovies.info',
    'awarefinance.com',
    'shurll.com',
    'acceptsearch.com',
    'signforcover.com',
    'raisengine.com',
    'rocketcarrental.com',
    'godsearchs.com',
    'listenhanced.com',
    'find24hs.com',
    'findinform.com',
    'sitesworlds.com',
    'rocketcarrental.com',
    'thedirsite.com',
    'getboook.com',
    'pokerarena88.com',
    'aquamelia.com',
    'beautyskintalks.com',
    'getmooovie.com',
    'getdriversss.com',
    'getsoooft.com',
    'getgamesss.com',
    'abrts.pro',
    'leadbit.biz',
    'efght.pro',
    'qyresearcheurope.com',
    'plusfreemaxfr.com',
    'getappmac.com',
    'getharlemhealthy.org',
    'goo.gl',
    'getmooovie.com',
    'marketreportscenter.com',
    'getsooft.com',
    'myowndom.ru',
    'print-mgn.ru',
    'wiki-data.ru',
    'velobog.ru',
    'mobisony.ru',
    'dzeroki.ru',
    'slimkor.ru',
    'kak-brosit-kyrit.ru',
    'jinyurl.com',
    'urlin.us',
    'capillus.com',
    'siteprofissional.com',
    'mitersawjudge.com',
    'mohajreen-jeeda.com',
    'jobberies.com',
    'bestfilms.site',
    'baystudios.ch',
    'elvenarhack.bid',
    'essencephskincare.com',
    'blog2learn.com',
    'superrugbyonline.net',
    'superrugby18.livejournal.com',
    'expertairco.com',
    'draesthetica.co.uk',
    'sphere.social',
    'saveabookmarks.xyz',
    '/t.co',
    'samuelsconstruction.build',
    'pmwares.com',
    'watchesofwales.co.uk',
    'zotero.org',
    'speakerdeck.com',
    'freesiteslike.com',
    'pusha.se',
    'vrootdownload.org',
    'rubberwebshop.nl',
    'restaurerlecorps.info',
    'discretthemes.info',
    'bride-forever.com',
    'simplesmetamorphoses.info',
    'mp3gain.com',
    'mp4gain.com',
    'ttlink.com',
    'onepost.cf',
    'getmefunds.com',
    'vikinail.pl',
    'typesofbeauty.info',
    'joie6portia93.bloglove.cc',
    'htgtea.com',
    'tblogz.com',
    'liveinternet.ru',
    '.diowebhost.com',
    '/yoursite.com',
    'reworkedgames.eu',
    'mp3gain.sourceforge.net',
    'pages10.com',
    'nudegirIs.info',
    'aidagirls.com',
    'alsoloves.com',
    'hotswishes.com',
    'instaphoto.club',
    'intimspace.com',
    'pornopoisk.info',
    'localmodels.online',
    'kaikki-mallit.com',
    'hotswishes.com',
    'movieblog.tumblr.com',
    'moviehdstream.wordpress.com',
    'moviehq.tumblr.com',
    'moviehq.webs.com',
    'moviehq.wordpress.com',
    'movieo.wordpress.com',
    'movieonline.tumblr.com',
    'movieonline.webs.com',
    'movieonline.wordpress.com',
    'movieonlinehd.tumblr.com',
    'movieonlinehd.webs.com',
    'movieonlinehd.wordpress.com',
    'movies.tumblr.com',
    'moviesf.tumblr.com',
    'moviesgodetia.com',
    'movieslinks4u',
    'moviesmount.com',
    'moviesmonster.biz',
    'moviesondesktop',
    'moviesonlinefree.biz',
    'moviestream.wordpress.com',
    'movieontop.com',
    'afllivestreaming.com.au',
    'londonolympiccorner',
    'nrllivestreaming.com.au',
    '24x7livestreamtvchannels.com',
    'www.edogo.us',
    'all4health.in',
    'watches4a.co.uk',
    'es.jennyjoseph.com',
    'allsportslive24x7.blogspot.com',
    'boxing-tv-2014-live-stream.blogspot.com',
    'amarblogdalima.blogspot.com',
    'www.officialtvstream.com.es',
    'topsalor.com',
    'busybo.org',
    'www.nowvideo.sx',
    '180upload.com',
    'allmyvideos.net',
    'busybo.org',
    'hdmovieshouse.biz',
    'sportblog.info',
    'psport.space',
    'discus.space',
    'euro2016.it.ua',
    'neymar.space',
    'espnstream.space',
    '2016.vn.u',
    'blogstream.space',
    'liveextratime.xyz',
    'thebestlive.xyz',
    'streamoffside.xyz',
    'sportmaster2014.page.tl',
    'bloggersdelight.dk',
    'watchsportslive.space',
    'freeforward.xyz',
    'live4sports.xyz',
    'streamfun.xyz',
    'angelfire.com',
    'streamtime.xyz',
    'futebol2star.com',
    'live2sport.com',
    'newssports.space',
    'onlineolympics.xyz',
    'liveolympics.xyz',
    'streamontv.xyz',
    'londonschedule.com',
    'onlineolympics.space',
    'sportwinning.xyz',
    'streamworld.xyz',
    'streamtop.xyz',
    'livechampion.xyz',
    'playstreams.xyz',
    'live4sport.xyz',
    'streampage.xyz',
    'calendarsport.space',
    'fsport.space',
    'euro2016.od.ua',
    'streambig.xyz',
    'sportprediction.xyz',
    'streamwork.xyz',
    'r041.donnael.com',
    '2016.lt.ua',
    'vipleague.se',
    'liveonline.company',
    'liveolympics.space',
    'seoandvideomarketing.com.au',
    'vipbox.sx',
    'germanypolandlivestream.club',
    'sportgoal.xyz',
    'ggdbsale.com',
    'gorillasteroids.eu',
    'watchlivesports.space',
    'penaltyshootout.xyz',
    'streamgroup.xyz',
    'streamnew.xyz',
    'cottonsport.space',
    'gosport.space',
    'streambest.xyz',
    'penaltyspot.xyz',
    'streamthe.xyz',
    'liveevents.name',
    'londonblog.work',
    'testcollections.com',
    'alfagy.com',
    'teravide1974.full-design.com',
    'selfnarhasbllaq1980-blog.logdown.com',
    'neipononchoi1984.suomiblog.com',
    'gemttranlonthe1985.blogzet.com',
    'pitchero.com',
    'blogolize.com',
    'lisbopholsven1974.thezenweb.com',
    'blogocial.com',
    'tinyblogging.com',
    'share.pho.to',
    'community.vietfun.com',
    'ockuderla1985.full-design.com',
    'unmosimla1978.total-blog.com',
    'gemttranlonthe1985.blogzet.com',
    'rapptubizboe1978.blogminds.com',
    'descduclighgon1973.full-design.com',
    'ricphosati1972.full-design.com',
    'fuddbluslanmaa1975.blogdigy.com',
    'smarforcute1976.blogdigy.com',
    'xn--90aizihgi.xn--p1ai',
    'tinyurl.com',
    'bit.ly',
    'bit.do',
    '123football.space',
    'bitly.com',
    'j.mp',
    'livestreaming.one',
    'livestreaming.life',
    'forbest.pw',
    'olizev.tdska2ll.ru',
    'tdska2ll.ru',
    'tdska1ll.ru',
    'tdska3ll.ru',
    'tdska4ll.ru',
    'ihmail.ru',
    'tdska5ll.ru',
    'tdska6ll.ru',
    'll.ru',
    'shorl.com',
    'scorestream.space',
    'bestsplayer.xyz',
    'worldwideevents.space',
    'worldseries.space',
    'best247chemist.net',
    '9tn.ru',
    'futbolkin2013.ru',
    'playnowstore.com',
    'qr-url.tk',
    'watchonlinerugby.net',
    'esecuritys.com',
    'rufile.no-ip.ca',
    'imzonline.com',
    'femeedia.com',
    'mediomatic.com',
    'savemoneyeasily.com',
    'option1pro.com',
    'perron07.nl',
    'movieonrails.com',
    'topmoviestoday.com',
    'playnowstore.com',
    'g-files.biz',
    'dawnloadonline.com',
    'thedirsite.com',
    'siteslocate.com',
    'mydrugdir.com',
    'find24hs.com',
    'veeble.org',
    'movieonrails.com',
    'bestmoviehd.net',
    'putmovies.info',
    'awarefinance.com',
    'shurll.com',
    'acceptsearch.com',
    'signforcover.com',
    'raisengine.com',
    'rocketcarrental.com',
    'godsearchs.com',
    'listenhanced.com',
    'find24hs.com',
    'findinform.com',
    'sitesworlds.com',
    'rocketcarrental.com',
    'thedirsite.com',
    'getboook.com',
    'pokerarena88.com',
    'aquamelia.com',
    'beautyskintalks.com',
    'getmooovie.com',
    'getdriversss.com',
    'getsoooft.com',
    'getgamesss.com',
    'abrts.pro',
    'leadbit.biz',
    'efght.pro',
    'qyresearcheurope.com',
    'plusfreemaxfr.com',
    'getappmac.com',
    'getharlemhealthy.org',
    'goo.gl',
    'getmooovie.com',
    'marketreportscenter.com',
    'getsooft.com',
    'myowndom.ru',
    'print-mgn.ru',
    'wiki-data.ru',
    'velobog.ru',
    'mobisony.ru',
    'dzeroki.ru',
    'slimkor.ru',
    'kak-brosit-kyrit.ru',
    'jinyurl.com',
    'urlin.us',
    'capillus.com',
    'siteprofissional.com',
    'mitersawjudge.com',
    'mohajreen-jeeda.com',
    'jobberies.com',
    'bestfilms.site',
    'baystudios.ch',
    'elvenarhack.bid',
    'essencephskincare.com',
    'blog2learn.com',
    'superrugbyonline.net',
    'superrugby18.livejournal.com',
    'expertairco.com',
    'draesthetica.co.uk',
    'sphere.social',
    'saveabookmarks.xyz',
    '/t.co',
    'samuelsconstruction.build',
    'pmwares.com',
    'watchesofwales.co.uk',
    'zotero.org',
    'speakerdeck.com',
    'freesiteslike.com',
    'pusha.se',
    'vrootdownload.org',
    'rubberwebshop.nl',
    'restaurerlecorps.info',
    'discretthemes.info',
    'bride-forever.com',
    'simplesmetamorphoses.info',
    'mp3gain.com',
    'mp4gain.com',
    'ttlink.com',
    'onepost.cf',
    'getmefunds.com',
    'vikinail.pl',
    'typesofbeauty.info',
    'joie6portia93.bloglove.cc',
    'htgtea.com',
    'tblogz.com',
    'liveinternet.ru',
    '.diowebhost.com',
    '/yoursite.com',
    'reworkedgames.eu',
    'mp3gain.sourceforge.net',
    'pages10.com',
    '2213ys.com',
    'goldengoosesneakershop.com',
    'howtocrazy.com',
    'divatsport-blog.info',
    '1xroyal.com',
    'yuctw.com',
    'bangalorehotescorts.in',
    'hx.269w.net',
    'fr.pdffile.org',
    'evistas.usc.edu.co',
    'caelt3.harrisburgu.edu',
    'seehd.pl',
    'benonscatering.co.uk',
    'itoolsdownload.info',
    'knoji.com',
    'srsintl.com',
    'www.trauringe-efes.de',
    'myassignmenthelp.com',
    'sgamepro.io',
    'brycewalkeruk.weebly.com',
    'windscreendiscounters.co.za',
    'bitcoinrevolutionreview.com',
    'freebetqq.com',
    'mpocash.com',
    'backofficevi.com',
    'thesingaporepropertyblog.wordpress.com',
    'www.winni.in',
    'www.viki.com',
    '//is.gd',
    '/answerfirst.com',
    'cewnote.com',
    'www.mastherapy.es',
    'pcpltd.com',
    '/nutoyshop.info',
    '/ppcair.com',
    'xe365.info',
    'www.foodpoisoningnews.com',
    '/freecracks.net',
    'www.noosaboatique.com.au',
    'www.bestbeds.co.nz',
    '/taxisweybridge.com',
    'www.speakwell.co.in',
    'www.islamickalajadu.com',
    'www.zxpanel.com',
    '.shop1.cz',
    '//Inve.one',
    'www.rsstop10.com',
    'www.cheapjerseys91.com',
    '.blogcountry.net',
    '/renewableenergyworld.com',
    '/www.anobii.com',
    '/atreointernational.com',
    '/thoushallnotwhine.com',
    '/boldwap.net',
    '/images16.fotki.com',
    '/www.mirrorcreator.com',
    '/ipaymu.com',
    '/www.deerasa.com',
    '/lp-distributors.com',
    '/www.radyoharunyahya.com',
    '/dominoqiu.co/',
    '/rejuvabrains.org/',
    '/images108.fotki.com',
    '/www.wizhez.com',
    'www.iamsport.org',
    '/justinbravo.com',
    '/alix92irina.blog5.net',
    '/usachinatraining.com',
    'prodid3gizi.poltekkes-malang.ac.id',
    '/www.getjealous.com',
    '/www.gdaca.com',
    '/bxxlacy864398.wordpress.com',
    '/vip126.cafe24.com',
    '/treltistconsi1972.wordpress.com',
    '/dansmoke.com/',
    '/www.boostmedia.in',
    'www.thechubbybuddy.com',
    '/anewwellnessspa.com',
    '/mercer82rios.wordpress.com',
    '/cadcamoffices.co.uk',
    '/carpetexperts.net',
    '/media4.picsearch.com',
    'slotsbonus777.com',
    'nudegirls.info',
    'aidagirls.com',
    'alsoloves.com',
    'hotswishes.com',
    'instaphoto.club',
    'intimspace.com',
    'pornopoisk.info',
    'localmodels.online',
    'kaikki-mallit.com',
    'hotswishes.com'
]);
