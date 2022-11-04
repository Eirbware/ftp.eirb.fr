INSERT INTO users (id, first_name, last_name, admin, added_by) VALUES
    ("aboin", "Alexandre", "Boin", true, NULL),
    ("araoult", "Antoine", "Raoult", true, "aboin"),
    ("jchabrier", "Julien", "Chabrier", true, "aboin"),
    ("mcaravati", "Matteo", "Caravati", true, "aboin"),
    ("mdupoux", "Mathieu", "Dupoux", true, "aboin"),
    ("anlerner", "Antoine", "Lerner", true, "aboin"),
    ("alducq", "Alexiane", "Ducq", false, "aboin"),
    ("mlabarre", "Martin", "Labarre", false, "aboin"),
    ("cguillaud", "Cassandra", "Guillaud", false, "aboin");

INSERT INTO sites (id, name, uid, dir) VALUES
    ("auth", "eirbAuth", "www-auth", "/srv/web/sites/auth"),
    ("bae", "BAE", "www-bae", "/srv/web/sites/bae"),
    ("bar", "Le Bar", "www-bar", "/srv/web/sites/bar"),
    ("bda", "BDA", "www-bda", "/srv/web/sites/bda"),
    ("bda-dev", "BDA (dev)", "www-bda-dev", "/srv/web/sites/bda-dev"),
    ("bde", "BDE", "www-bde", "/srv/web/sites/bde"),
    ("bill", "Bill'eirb", "www-bill", "/srv/web/sites/bill"),
    ("binks", "Binks", "www-binks", "/srv/web/sites/binks"),
    ("cook", "Cook'eirb", "www-cook", "/srv/web/sites/cook"),
    ("cors", "Cors'eirb", "www-cors", "/srv/web/sites/cors"),
    ("discord", "Discord", "www-discord", "/srv/web/sites/discord"),
    ("eirb", "eirb.fr", "www-eirb", "/srv/web/sites/eirb"),
    ("eirbot", "Eirbot", "www-eirbot", "/srv/web/sites/eirbot"),
    ("eirbware", "Eirbware", "www-eirbware", "/srv/web/sites/eirbware"),
    ("eirlab", "Eirlab", "www-eirlab", "/srv/web/sites/eirlab"),
    ("eirsport", "EirSport", "www-eirsport", "/srv/web/sites/eirsport"),
    ("explorat", "Explorat'eirb", "www-explorat", "/srv/web/sites/explorat"),
    ("facebook", "Facebook", "www-facebook", "/srv/web/sites/facebook"),
    ("ftp", "ftp.eirb.fr", "www-ftp", "/srv/web/sites/ftp"),
    ("gala", "Gala Mos'fête", "www-gala", "/srv/web/sites/gala"),
    ("gcc", "GCC", "www-gcc", "/srv/web/sites/gcc"),
    ("internship", "internship.eirb.fr", "www-internship", "/srv/web/sites/internship"),
    ("listes", "Listes mail", "www-listes", "/srv/web/sites/listes"),
    ("milit", "Milit'eirb", "www-milit", "/srv/web/sites/milit"),
    ("oeno", "Œno", "www-oeno", "/srv/web/sites/oeno"),
    ("pix", "PixEirb", "www-pix", "/srv/web/sites/pix"),
    ("radio", "CheriF'eirb", "www-radio", "/srv/web/sites/radio"),
    ("rams", "Rams'eirb", "www-rams", "/srv/web/sites/rams"),
    ("revolution", "Révolutionn'eirb", "www-revolution", "/srv/web/sites/revolution"),
    ("scan", "Scan'eirb", "www-scan", "/srv/web/sites/scan"),
    ("shibboleth", "shibboleth.eirb.fr", "www-shibboleth", "/srv/web/sites/shibboleth"),
    ("theatre", "Club Théâtre", "www-theatre", "/srv/web/sites/theatre"),
    ("univ", "Univ'eirb", "www-univ", "/srv/web/sites/univ"),
    ("unlock", "Unlock", "www-unlock", "/srv/web/sites/unlock"),
    ("vault", "vault.eirb.fr", "www-vault", "/srv/web/sites/vault"),
    ("vote", "vote.eirb.fr", "www-vote", "/srv/web/sites/vote"),
    ("wazuh", "wazuh.eirb.fr", "www-wazuh", "/srv/web/sites/wazuh"),
    ("west", "West'eirb", "www-west", "/srv/web/sites/west"),
    ("wiki", "Wiki", "www-wiki", "/srv/web/sites/wiki"),
    ("zik", "Club Zik", "www-zik", "/srv/web/sites/zik");

INSERT INTO users_sites (user_id, site_id, authorized_by, authorized_at) VALUES
    ("alducq", "bae", "aboin", NOW()),
    ("mcaravati", "bda", "aboin", NOW()),
    ("mcaravati", "bda-dev", "aboin", NOW()),
    ("aboin", "bde", "aboin", NOW()),
    ("mlabarre", "cook", "aboin", NOW()),
    ("aboin", "discord", "aboin", NOW()),
    ("aboin", "eirb", "aboin", NOW()),
    ("aboin", "eirbware", "aboin", NOW()),
    ("aboin", "facebook", "aboin", NOW()),
    ("aboin", "ftp", "aboin", NOW()),
    ("cguillaud", "oeno", "aboin", NOW()),
    ("aboin", "scan", "aboin", NOW()),
    ("aboin", "univ", "aboin", NOW()),
    ("mcaravati", "unlock", "aboin", NOW()),
    ("mcaravati", "zik", "aboin", NOW());