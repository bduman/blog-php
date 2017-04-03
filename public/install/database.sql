SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `admin` (
  `username` varchar(45) COLLATE utf8_turkish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `admin_UNIQUE` (`username`),
  UNIQUE KEY `token_UNIQUE` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

INSERT INTO `admin` (`username`, `password`, `token`) VALUES
('admin', '$2y$10$ng8/jnvdhI7h1a5QowmR0OaSXrfN7jBfooFz.BBBdZ4tPnvEf5S8W', 'c326b5b658ddc665cc85e5da8467b2de');

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_turkish_ci NOT NULL,
  `url` varchar(45) COLLATE utf8_turkish_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `showcase` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci AUTO_INCREMENT=10 ;

INSERT INTO `category` (`id`, `name`, `url`, `enabled`, `showcase`, `created`) VALUES
(1, 'kategori bu bak', 'kategori-bu-bak-1', 1, 1, '2017-03-14 17:08:56'),
(9, 'yeni kategori oluşturdum', 'yeni-kategori-olusturdum-9', 1, 1, '2017-03-20 21:55:10');

CREATE TABLE IF NOT EXISTS `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(145) COLLATE utf8_turkish_ci NOT NULL,
  `article` text COLLATE utf8_turkish_ci,
  `url` varchar(145) COLLATE utf8_turkish_ci DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `published` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_post_category1_idx` (`category_id`),
  KEY `published` (`published`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci AUTO_INCREMENT=4 ;

INSERT INTO `post` (`id`, `title`, `article`, `url`, `category_id`, `published`, `enabled`) VALUES
(1, 'Başlık 1', '<p><br></p><p><em><img src="https://www.froala.com/assets/froala-editor-19e0569277293a9f50b9e73649ef2f56.svg" style="width: 300px;" class="fr-fic fr-dib" alt="froala editör">Lorem ipsu</em>m <strong>dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in volup</strong>tate <em>velit es</em>se c<u>illum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est la</u>borum.</p><p><br></p><p><img src="https://i.froala.com/download/be6455c637c1215db966d967936f62952c03793f.png?1490481027" style="width: 300px;" class="fr-fic fr-dib" alt="github"></p>', 'baslik-1-1', 1, '2017-03-14 17:10:00', 1),
(2, 'Başlık 2', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 'baslik-2-2', 1, '2017-03-15 17:10:00', 1),
(3, 'Başlık 3', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 'baslik-3-3', 1, '2017-03-16 17:10:00', 1);

CREATE TABLE IF NOT EXISTS `post_has_tag` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `fk_post_has_tag_tag1_idx` (`tag_id`),
  KEY `fk_post_has_tag_post1_idx` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

INSERT INTO `post_has_tag` (`post_id`, `tag_id`) VALUES
(1, 1),
(2, 1),
(1, 2),
(2, 2),
(3, 2),
(3, 7);

CREATE TABLE IF NOT EXISTS `site` (
  `var` varchar(45) COLLATE utf8_turkish_ci NOT NULL COMMENT 'reserved:name,description,links',
  `val` varchar(300) COLLATE utf8_turkish_ci NOT NULL,
  PRIMARY KEY (`var`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

INSERT INTO `site` (`var`, `val`) VALUES
('ads_click', 'http://google.com.tr'),
('ads_text', '<h2>Reklam yazısı</h2>\r\n<p>Detaylı açıklama buraya gelecek</p>'),
('description', 'Açıklama buraya gelecek'),
('disqusUsername', 'bduman'),
('keywords', 'kelime1, kelime2, kelime3, kelime4'),
('name', 'blog');

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(45) COLLATE utf8_turkish_ci NOT NULL,
  `url` varchar(45) COLLATE utf8_turkish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci AUTO_INCREMENT=8 ;

INSERT INTO `tag` (`id`, `tag`, `url`) VALUES
(1, 'Etiket 1', 'etiket-1'),
(2, 'Etiket 2', 'etiket-2'),
(4, 'qwe', 'qwe-4'),
(5, 'selaaam', 'selaaam-5'),
(6, 'etiket 3', 'etiket-3-6'),
(7, 'ehühühühü', 'ehuhuhuhu-7');


ALTER TABLE `post`
  ADD CONSTRAINT `fk_post_category1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `post_has_tag`
  ADD CONSTRAINT `fk_post_has_tag_post1` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_post_has_tag_tag1` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
