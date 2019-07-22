-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 22. Jul 2019 um 12:57
-- Server-Version: 10.1.40-MariaDB
-- PHP-Version: 7.1.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `widmedia`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `category` int(1) NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `language`
--

CREATE TABLE `language` (
  `id` int(11) NOT NULL,
  `en` text NOT NULL,
  `de` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `language`
--

INSERT INTO `language` (`id`, `en`, `de`) VALUES
(1, 'About', 'Über'),
(2, 'some picture of', 'ein Foto von'),
(3, 'Contact', 'Kontakt'),
(4, 'is developed by Daniel Widmer. Please find the complete code at', 'wird durch Daniel Widmer entwickelt. Den gesamten source code findet man unter'),
(5, 'Contact (German, English)', 'Kontakt (Deutsch, Englisch)'),
(6, 'Data privacy', 'Datenschutz'),
(7, 'Be aware: without password protection for your account, all your user data are openly visible and may be edited. When using the password protection, widmedia tries to secure your data as good as possible, however, widmedia cannot guarantee full protection.', 'Achtung: ohne Passwortschutz sind all Ihre Daten öffentlich zugänglich und können editiert oder gelöscht werden. Mit Passwortschutz versucht widmedia diese Daten so gut es geht vor unberechtigten Zugriffen zu schützen. Leider kann widmedia darauf keine Garantie erteilen.'),
(8, 'Only data required for the functionality of this website is stored. The data base layout and structure as well as the underlying code is available for inspection on the open source', 'Gespeichert werden nur Daten, die für diese Website nötig sind. Sowohl das Datenbanklayout und die Struktur als auch der verwendete Code sind öffentlich zugänglich als open source'),
(9, 'project', 'Projekt'),
(10, 'The data will not be sold or transferred otherwise to any external party.', 'Ihre Daten werden nicht verkauft oder andersweitig an Drittpersonen weitergegeben.'),
(11, 'On the other hand, widmedia cannot guarantee regular backups of your data, your data might be deleted or get lost in a different way.', 'Andererseits kann widmedia keine regelmässigen Backups garantieren. Ihre Daten können gelöscht werden oder verloren gehen.'),
(12, 'Do not rely on', 'Verlassen Sie sich nicht auf '),
(13, 'as your only data source and do not store any sensitive information on this site.', 'als einzige Datenquelle und speichern Sie keine kritischen Daten auf dieser Seite.'),
(14, 'will not be held accountable for the material created, stored or available on this site, especially the links to external sites.', 'ist nicht zuständig für irgendwelche Nutzerdaten auf dieser Seite, insbesondere die Links auf externe Seiten.'),
(15, 'External sources', 'Externe Quellen'),
(16, 'No external sources are used.', 'Es werden keine externen Quellen verwendet.'),
(17, 'Financing', 'Finanzierung'),
(18, '...well, there is none. If you like to contribute, please contact me:', '...hmm, gibt es nicht wirklich. Wenn Sie etwas beitragen möchten, können Sie mich hier erreichen:'),
(19, 'link has been updated', 'Link wurde aktualisiert'),
(20, 'category has been updated', 'Kategoriename wurde aktualisiert'),
(21, 'link has been deleted', 'Link wurde gelöscht'),
(22, 'counters have been reset to 0', 'Zähler wurden zurückgesetzt'),
(23, 'link has been added', 'Link wurde hinzugefügt'),
(24, 'user account has been updated', 'User account wurde aktualisiert'),
(25, 'logout successful, cookie has been deleted as well', 'Logout ok, Cookies wurden ebenfalls gelöscht'),
(26, 'updated', 'aktualisiert'),
(27, 'edit links', 'Links anpassen'),
(28, 'edit user account ', 'Nutzerkonto'),
(29, 'new account', 'neuer Account'),
(30, 'Testuser cannot be changed', 'Testuser kann nicht angepasst werden'),
(31, 'I am sorry but when logged in as the testuser, you cannot change any settings. Might want to open your own account?', 'Sorry aber als Testuser darf man nichts anpassen. Könnte ich dich eventuell für einen eigenen Account begeistern?'),
(32, 'open account', 'Account eröffnen'),
(33, '\"Something\" at step ', '\"Irgend etwas\" während Schritt '),
(34, ' went wrong when processing user input data (very helpful error message, I know...). Might try again? <br>If you think you did everything right, please send me an email:', ' lief falsch beim Verarbeiten der input Daten (Extrem hilfreiche Fehlermeldung, ich weiss...). Vielleicht probierst dus nochmals? <br>Falls du das Gefühl hast, dass du alles richtig gemacht hast, schreib mir doch bitte eine Email:'),
(35, 'What would you like to edit?', 'Was möchtest du anpassen?'),
(36, 'Category ', 'Kategorie '),
(37, 'set all link counters to zero', 'alle Links-Zähler auf 0 setzen'),
(38, 'add new link', 'neuen Link hinzufügen'),
(39, 'save', 'speichern'),
(40, 'delete', 'löschen'),
(41, 'change category name', 'Kategorie umbenennen'),
(42, 'Add a new link', 'Neuen Link hinzufügen'),
(43, 'Wrong URL', 'Falsche URL'),
(44, 'For the URL input, you need to have something in the format \"http://somewebsite.ch\" or \"https://somewebsite.ch\"', 'Als URL brauche ich etwas im Stil von \"http://somewebsite.ch\" oder \"https://somewebsite.ch\"'),
(45, 'Edit', 'Anpassen'),
(46, 'last login: ', 'letzter Login: '),
(47, 'password protection for this account', 'Passwortschutz für diesen Account'),
(48, 'Please be aware: when not using a password, everybody can log into this account and edit information or delete the account itself', 'Achtung: falls du kein Passwort verwendest, kann jeder auf deinen Account zugreifen und Sachen ändern oder den Account löschen'),
(49, 'old password', 'altes Passwort'),
(50, 'new password', 'neues Passwort'),
(51, 'save changes', 'Änderungen speichern'),
(52, 'delete this account (without any further confirmation)', 'Account löschen (ohne irgendwelche weitere Nachfragen)'),
(53, 'Deleted the account', 'Account gelöscht'),
(54, 'Deleted userid: ', 'Gelöschte Userid: '),
(55, 'go back to', 'zurück zu'),
(56, 'User statistics ', 'User Statistik '),
(57, 'number of active users (last login is less than 1 month old)', 'Anzahl aktiver User (letzer Login ist nicht älter als 1 Monat)'),
(58, 'Mar', 'Mär'),
(59, 'May', 'Mai'),
(60, 'Oct', 'Okt'),
(61, 'Dec', 'Dez'),
(62, 'your email', 'deine Email'),
(63, 'your password', 'dein Passwort'),
(64, 'create your free account', 'Gratisaccount eröffnen'),
(65, 'a simple and free customizable start page, your personal link collection', 'simpel, gratis und frei konfigurierbar: deine Linksammlung als neue Startseite'),
(66, 'your personal list of links', 'deine persönliche Linkliste'),
(67, 'sorted by occurence', 'sortiert nach Häufigkeit'),
(68, 'links open on new tab', 'Links öffnen neue Tabs'),
(69, 'edit and add your own links', 'pass die Links an, füge neue hinzu'),
(70, 'easy login', 'einfacher Login'),
(71, 'try it first', 'probiers einfach mal aus'),
(72, 'test user', 'Testuser'),
(73, 'or', 'oder'),
(74, 'Your list of links', 'Alle deine Links'),
(75, 'Click it', 'Klick drauf'),
(76, 'The page opens in a new tab', 'Die Seite öffnet sich in einem neuen Tab'),
(77, 'Edit your links, add a new one', 'Editier deine Links, füge einen neuen hinzu'),
(78, 'There it is, your new link', 'Et voilà, dein neuer Link'),
(79, '...and open that one', '...und er funktioniert'),
(80, 'Go for it', 'Los geht\'s'),
(81, 'open a new free account', 'erstell deinen gratis Account'),
(82, 'Try it first', 'Zuerst mal ausprobieren'),
(83, 'log in as the test user', 'Login als Testuser'),
(84, 'Password', 'Passwort'),
(85, 'save log in information for 4 weeks in a cookie', 'Log in infos während 4 Wochen in einem Cookie speichern'),
(86, '(This might me a good moment to store this page as your browser starting page. Unfortunately I cannot provide you a link to do so. Modern browsers will not allow it.)', '(Das wäre jetzt ein guter Moment um diese Seite als deine Browser-Startseite zu setzen. Leider kann ich das nicht automatisch, kein halbwegs moderner Browser erlaubt das.)'),
(87, '(TODO) forgot my password', '(TODO) Passwort vergessen'),
(88, 'go to login page', 'zur Loginseite'),
(89, 'Your account has been created', 'Dein Account wurde erstellt'),
(90, 'Congratulations and thanks. Your account is now ready. Please ', 'Merci und Gratulation. Dein Account wurde erstellt.'),
(91, 'you selected a password but the password is too short (at least 4 characters)', 'Du hast den Passwortschutz gewählt, aber das Passwort ist zu kurz (mindestens 4 Zeichen)'),
(92, 'An account with this email address is already existing', 'Es existiert bereits ein Account mit dieser Emailadresse'),
(93, 'Verified', 'Verifiziert'),
(94, 'Thank you. Your email address has been verified and your account is now fully functional. Please', 'Merci. Deine Emailadresse wurde verifiziert und dein Account ist jetzt voll funktionsfähig.'),
(95, 'Thank you for opening a free account on widmedia.ch/start.', 'Dankeschön dass du deinen Gratisaccount auf widmedia.ch/start eröffnet hast.'),
(96, 'You need to confirm your email address within 24 hours to fully use your account. Please click on the link below to do so:', 'Du musst deine Emailadresse innerhalb von 24 Stunden bestätigen. Bitte klicke dazu auf den untenstehenden Link:'),
(97, 'You did select password protection for your account. Please use the form on', 'Du hast einen Account mit Passwortschutz gewählt. Auf'),
(98, 'to log in.', 'kannst du dich einloggen.'),
(99, 'You did not select password protection. This means you (and, btw. everybody else) may login with this link:', 'Du hast einen Account ohne Passwortschutz gewählt. Das heisst du (und übrigens jeder/jede) kann sich mit diesem Link einloggen:'),
(100, 'Please store this link for future use as a bookmark or maybe your browser starting page.', 'Bitte speichere diesen Link entweder als Bookmark oder direkt als Startseite deines Browsers.'),
(101, 'Have fun and best regards,', 'Viel Spass und freundliche Grüsse'),
(102, 'from', 'von'),
(103, 'Your new account on widmedia.ch/start', 'Dein neuer Account auf widmedia.ch/start'),
(104, 'Your email address has not yet been verified. Please do so within 24 hours, otherwise this account will be deleted.', 'Deine Emailadresse wurde noch nicht verifiziert. Bitte mach das innerhalb von 24 Stunden, ansonsten wird dieser Account wieder gelöscht.'),
(105, 'This is the (somewhat limited) test account. Get your own account?', 'Das ist der (bisschen eingeschränkte) Testaccount. Willst du einen eigenen Account?');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `links`
--

CREATE TABLE `links` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `text` text NOT NULL,
  `link` text NOT NULL,
  `cntTot` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `lastLogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hasPw` tinyint(1) NOT NULL,
  `pwHash` char(255) NOT NULL,
  `randCookie` char(64) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `verCode` char(64) NOT NULL,
  `verDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `userStat`
--

CREATE TABLE `userStat` (
  `id` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `numUser` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `language`
--
ALTER TABLE `language`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `userStat`
--
ALTER TABLE `userStat`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `language`
--
ALTER TABLE `language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT für Tabelle `links`
--
ALTER TABLE `links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `userStat`
--
ALTER TABLE `userStat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
