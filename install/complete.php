<?php
if(file_exists('../setting.php')){
    die('설정 파일이 이미 있습니다.');
}

#데이터베이스 연결 테스트
extract($_POST, EXTR_PREFIX_ALL, "P");
$conn = mysqli_connect($P_db, $P_dbid, $P_dbpw, $P_dbname);
if (!$conn) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    echo '데이터베이스 관련 정보를 다시 확인해주세요.';
    exit;
}

#데이터베이스 구조 생성
    $sql = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
    SET time_zone = "+00:00";'.
    "CREATE TABLE `_account` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` varchar(20) NOT NULL COMMENT '이용자 아이디',
      `name` varchar(20) NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `password` text NOT NULL COMMENT '비밀번호',
      `mail` text NOT NULL COMMENT '사용자 메일',
      `mailAuth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '이메일 인증 여부',
      `lastIp` text NOT NULL COMMENT '마지막으로 사용한 IP 주소',
      `isAdmin` tinyint(1) NOT NULL COMMENT '관리 권한 소지 유무',
      `canUpload` tinyint(1) NOT NULL DEFAULT '0' COMMENT '업로드 권한 여부',
      `siteBan` tinyint(1) NOT NULL DEFAULT '0' COMMENT '사이트 전체 차단 여부',
      `autoLogin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'ip / UA 기반 자동 로그인 허용 여부',
      `userAgent` text,
      `userIntro` varchar(500) NOT NULL COMMENT '이용자 소개글',
      `point` int(11) NOT NULL COMMENT '활동 정산 포인트'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='이용자 계정 정보';

    CREATE TABLE `_ad` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` text NOT NULL COMMENT '이용자 아이디',
      `name` text NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `ad` text NOT NULL,
      `link` varchar(250) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='광고 출력';

    CREATE TABLE `_article` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `title` varchar(1000) NOT NULL COMMENT '글 제목',
      `namespace` varchar(50) DEFAULT NULL,
      `content` longtext NOT NULL,
      `lastEdit` datetime DEFAULT NULL,
      `whoEdited` text,
      `viewCount` bigint(20) NOT NULL DEFAULT '0',
      `ACL` varchar(10) DEFAULT NULL,
      `execute` text
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='위키 문서';

    CREATE TABLE `_auth` (
      `num` bigint(20) NOT NULL,
      `begin` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '시작 일시',
      `type` varchar(10) NOT NULL COMMENT '유형',
      `key` text NOT NULL,
      `value` varchar(20) NOT NULL COMMENT '값',
      `end` datetime DEFAULT NULL COMMENT '종료 일시'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='인증에 사용됩니다.';

    CREATE TABLE `_board` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` text NOT NULL COMMENT '이용자 아이디',
      `name` text NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `slug` varchar(50) NOT NULL COMMENT '게시판 아이디',
      `title` varchar(50) NOT NULL COMMENT '게시판 이름',
      `nickTitle` varchar(5) NOT NULL,
      `boardIntro` varchar(500) NOT NULL COMMENT '게시판 소개글',
      `subs` int(11) NOT NULL DEFAULT '0' COMMENT '구독자 수',
      `related` text COMMENT '연관 게시판',
      `notice` text COMMENT '상단 안내문구입니다.',
      `keeper` text COMMENT '게시판 보조 관리인',
      `kicked` text COMMENT '각 게시판 추방 목록',
      `icon` text,
      `option` varchar(5) DEFAULT NULL,
      `rct` tinyint(1) NOT NULL DEFAULT '1',
      `tagSet` varchar(60) NOT NULL DEFAULT '기본, 잡담'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='게시판';

    CREATE TABLE `_comment` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` text NOT NULL COMMENT '이용자 아이디',
      `name` text NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `content` varchar(500) NOT NULL,
      `from` int(11) NOT NULL,
      `childOf` int(11) DEFAULT NULL,
      `parentNum` int(11) DEFAULT NULL,
      `mail` text,
      `voteCount_Up` int(11) NOT NULL,
      `voteCount_Down` int(11) NOT NULL,
      `blameCount` int(11) NOT NULL DEFAULT '0' COMMENT '신고 누적 수',
      `isEdited` datetime DEFAULT NULL,
      `whoEdited` text
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='일반 댓글';

    CREATE TABLE `_content` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` text NOT NULL COMMENT '이용자 아이디',
      `name` text NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `mail` text COMMENT '이메일',
      `title` varchar(100) NOT NULL COMMENT '글 제목',
      `content` mediumtext NOT NULL,
      `board` text NOT NULL,
      `boardName` text NOT NULL COMMENT '출신 게시판의 애칭/약칭',
      `category` text COMMENT '글 종류',
      `rate` varchar(2) NOT NULL DEFAULT 'PG' COMMENT '게시글의 등급입니다. (G/PG/R)',
      `staffOnly` text COMMENT '열람 허가 대상. 쉼표로 구분.',
      `ip` text NOT NULL,
      `isEdited` datetime DEFAULT NULL,
      `whoEdited` text,
      `voteCount_Up` int(11) NOT NULL DEFAULT '0',
      `voteCount_Down` int(11) NOT NULL DEFAULT '0',
      `viewCount` bigint(20) NOT NULL DEFAULT '0',
      `commentCount` int(11) NOT NULL DEFAULT '0',
      `isMarkdown` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Markdown 적용 여부',
      `isMedia` tinyint(1) DEFAULT NULL COMMENT '1=사진, 2=동영상, 3=유튜브',
      `offNotify` tinyint(1) NOT NULL DEFAULT '0',
      `hideMain` tinyint(1) DEFAULT NULL,
      `actmeter` datetime DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='일반 게시글';

    CREATE TABLE `_discuss` (
      `num` int(11) NOT NULL,
      `at` datetime NOT NULL,
      `title` text NOT NULL,
      `discussName` text NOT NULL,
      `id` text NOT NULL,
      `status` varchar(10) NOT NULL,
      `lastEdit` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='위키 토론';

    CREATE TABLE `_discussThread` (
      `num` bigint(20) NOT NULL,
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `origin` int(11) NOT NULL,
      `id` text NOT NULL,
      `name` text NOT NULL,
      `content` text NOT NULL,
      `status` varchar(10) NOT NULL DEFAULT 'ACTIVE'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE `_fnbcon` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` text COMMENT '이용자 아이디',
      `name` text NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `folder` varchar(10) NOT NULL COMMENT '이모티콘 폴더명',
      `title` text NOT NULL COMMENT '이모티콘 이름',
      `content` text NOT NULL COMMENT '이모티콘 설명',
      `ext` varchar(4) NOT NULL DEFAULT 'png' COMMENT '확장자',
      `count` int(11) NOT NULL COMMENT '이모티콘 개수 (1부터)',
      `use` int(11) NOT NULL COMMENT '사용자 수',
      `cost` smallint(5) NOT NULL COMMENT '이모티콘 가격'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='기본 테이블 셋';

    CREATE TABLE `_history` (
      `num` int(11) NOT NULL,
      `title` text NOT NULL,
      `id` text NOT NULL,
      `name` text NOT NULL,
      `rev` longtext,
      `comment` varchar(100) DEFAULT NULL,
      `at` datetime NOT NULL,
      `modify?` varchar(10) DEFAULT NULL,
      `ACL` varchar(10) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='위키 편집 기록';

    CREATE TABLE `_ipban` (
      `num` bigint(20) NOT NULL COMMENT '순번',
      `ip` text NOT NULL COMMENT '아이피 주소'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='아이피 기반 비회원 차단';

    CREATE TABLE `_log` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` text NOT NULL COMMENT '이용자 아이디',
      `name` text NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `ip` text NOT NULL,
      `isSuccess` tinyint(1) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='로그인 기록';

    CREATE TABLE `_ment` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` text NOT NULL COMMENT '이용자 아이디',
      `name` text NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `value` text,
      `target` text,
      `cmt_id` text NOT NULL,
      `reason` text,
      `ip` text,
      `isSuccess` tinyint(1) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='알림 / 호출';

    CREATE TABLE `_othFunc` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` text NOT NULL COMMENT '이용자 아이디',
      `name` text NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `value` text,
      `target` text,
      `reason` text,
      `ip` text,
      `isSuccess` tinyint(1) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='기타 중요도 낮은 기능';

    CREATE TABLE `_setting` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `siteTitle` text NOT NULL COMMENT '사이트 제목',
      `siteDesc` text NOT NULL COMMENT '사이트 설명',
      `siteFab` text NOT NULL COMMENT '사이트 로고(파비콘)',
      `sitePath` text NOT NULL COMMENT '사이트 경로',
      `siteLang` varchar(25) NOT NULL DEFAULT 'ko-KR' COMMENT '언어 설정',
      `siteEmMail` varchar(255) NOT NULL COMMENT '비상 이메일',
      `pageHead` mediumtext NOT NULL COMMENT '<head> 태그 속에 삽입될 코드',
      `pageLeft` text COMMENT '페이지 좌측에 표시될 내용',
      `pageColor` varchar(25) NOT NULL DEFAULT '#5998d6' COMMENT '탭 색상 (theme-color)',
      `pageSubColor` text NOT NULL COMMENT '보조 색상',
      `pageBgColor` text NOT NULL COMMENT '배경 색상',
      `pageFooter` mediumtext NOT NULL COMMENT '페이지 하단 글',
      `siteTimezone` varchar(25) NOT NULL DEFAULT 'Asia/Seoul' COMMENT '기본 시간대',
      `recentHide` text COMMENT '종합 글 목록 미노출 게시판'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='사이트 전체설정';

    CREATE TABLE `_skinSet` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `author` text NOT NULL COMMENT '제작자 표시',
      `name` text NOT NULL COMMENT '스킨 이름',
      `type` char(10) NOT NULL COMMENT '스킨 유형',
      `version` varchar(25) NOT NULL COMMENT '스킨 버전',
      `Desc` text NOT NULL COMMENT '스킨 설명',
      `DefaultColor` text NOT NULL COMMENT '기본 색상',
      `Subcolor` text NOT NULL COMMENT '보조 색상'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='스킨 개별 설정';

    CREATE TABLE `_upload` (
      `filename` text NOT NULL,
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `ip` text NOT NULL,
      `num` int(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='업로드 기록';

    CREATE TABLE `_userSet` (
      `num` int(11) NOT NULL COMMENT '정렬 및 조회를 위한 번호',
      `id` text NOT NULL COMMENT '이용자 아이디',
      `name` text NOT NULL COMMENT '이용자 닉네임',
      `type` char(10) NOT NULL COMMENT '유형',
      `at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '수행 시간',
      `fnbcon` varchar(500) NOT NULL DEFAULT 'default,test',
      `homepage` text COMMENT '홈페이지',
      `editor` varchar(1) DEFAULT NULL,
      `subs` text,
      `display_none` text,
      `hideAdv` tinyint(1) NOT NULL DEFAULT '0',
      `listNum` varchar(2) DEFAULT NULL,
      `tempSave` text COMMENT '임시 저장',
      `wikiColor` varchar(13) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='이용자 개인 설정';


    ALTER TABLE `_account`
      ADD PRIMARY KEY (`num`),
      ADD UNIQUE KEY `id` (`id`),
      ADD UNIQUE KEY `name` (`name`);

    ALTER TABLE `_ad`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_article`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_auth`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_board`
      ADD PRIMARY KEY (`num`),
      ADD UNIQUE KEY `title` (`title`),
      ADD UNIQUE KEY `slug` (`slug`);

    ALTER TABLE `_comment`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_content`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_discuss`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_discussThread`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_fnbcon`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_history`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_ipban`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_log`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_ment`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_othFunc`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_setting`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_skinSet`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_upload`
      ADD PRIMARY KEY (`num`);

    ALTER TABLE `_userSet`
      ADD PRIMARY KEY (`num`);


    ALTER TABLE `_account`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_ad`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_article`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_auth`
      MODIFY `num` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
    ALTER TABLE `_board`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_comment`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_content`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_discuss`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
    ALTER TABLE `_discussThread`
      MODIFY `num` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
    ALTER TABLE `_fnbcon`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_history`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
    ALTER TABLE `_ipban`
      MODIFY `num` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '순번', AUTO_INCREMENT=0;
    ALTER TABLE `_log`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_ment`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_othFunc`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_setting`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_skinSet`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;
    ALTER TABLE `_upload`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
    ALTER TABLE `_userSet`
      MODIFY `num` int(11) NOT NULL AUTO_INCREMENT COMMENT '정렬 및 조회를 위한 번호', AUTO_INCREMENT=0;";
    /* SQL 쿼리 질의 */
    if (mysqli_multi_query($conn, $sql)) {
        do {
            if ($result = mysqli_store_result($conn)) {
                mysqli_free_result($result);
            }
        } while (mysqli_next_result($conn));
    }


#데이터베이스 기본 내용 삽입
    /* 사이트 기본 설정 */
    $sql = "INSERT INTO `_setting` (`type`, `at`, `siteTitle`, `siteDesc`, `siteFab`, `sitePath`, `siteLang`, `siteEmMail`, `pageHead`, `pageLeft`, `pageColor`, `pageSubColor`, `pageBgColor`, `pageFooter`, `siteTimezone`, `recentHide`)
    VALUES ('board', NOW(), '$P_siteTitle', '$P_siteDesc', '/icon.png', '$P_sitePath', '$P_siteLang', '$P_siteEmMail', '$P_pageHead', NULL, '$P_pageColor', '$P_pageSubColor', '$P_pageBgColor', '$P_pageFooter', '$P_siteTimezone', NULL);";
    #($P_wiki == 'yes'){
        /* 위키 생성 */
        $sql .= "INSERT INTO `_setting` (`type`, `at`, `siteTitle`, `siteDesc`, `siteFab`, `sitePath`, `siteLang`, `siteEmMail`, `pageHead`, `pageLeft`, `pageColor`, `pageSubColor`, `pageBgColor`, `pageFooter`, `siteTimezone`, `recentHide`)
        VALUES ('wiki', NOW(), '$P_siteTitle Wiki', '$P_siteDesc', '', '$P_sitePath', '$P_siteLang', '$P_siteEmMail', '$P_pageHead', NULL, '$P_pageColor', '$P_pageSubColor', '$P_pageBgColor', '$P_pageFooter', '$P_siteTimezone', NULL);
        INSERT INTO
          `_article`(`num`,`type`,`at`,`title`,`namespace`,`content`,`lastEdit`,`whoEdited`,`viewCount`,`ACL`,`execute`)
        VALUES(
          -10,
          'SpecialDOC',
          '2020-05-22 16:43:28',
          '버전 정보',
          '___SPECIAL___',
          'FNBase Engine - Fully Non-commercial Board system. (c) 2020 FNBase Team. ---- OpenFNB는 [[밖:https://github.com/FNBase/FNBase-Engine|FNBase Engine]]의 부속 기능인 위키 부분을 테스트하고 보완해나가고자 만들어진 [[위키위키]]입니다. FNBase 개발진은 이 프로그램이 모두에게 도움이 되길 바라지만, 이 프로그램이 정상적으로 작동하거나 도움을 줄 것이라고 보증하지 않습니다. 만약 이 프로그램이 마음에 드신다면, 기여해주세요. 다국어 번역과 더 나은 작동 방식이 필요합니다. ----',
          '2020-09-19 02:41:21',
          'AUTO',
          '0',
          'none',
          'version'
        ),(
          -9,
          'SpecialDOC',
          '2020-05-21 10:58:10',
          '로그인',
          '___SPECIAL___',
          '___SITENAME___ 계정이 없으신가요? [[픈:/register|만들어보세요!]]',
          '2020-05-22 12:44:02',
          'AUTO',
          '0',
          'none',
          'login'
        ),(
          -8,
          'SpecialDOC',
          '2020-05-21 10:58:10',
          '특수 문서 목록',
          '___SPECIAL___',
          'OpenFNB의 기능들을 나열해놓았습니다.',
          '2020-05-21 10:58:10',
          'AUTO',
          '0',
          'none',
          'list'
        ),(
          -7,
          'SpecialDOC',
          '2020-05-21 10:58:10',
          '최근 바뀜',
          '___SPECIAL___',
          '최근 변경된 문서들의 목록입니다..',
          '2020-05-21 10:58:10',
          'AUTO',
          '0',
          'none',
          'recent'
        ),(
          -6,
          'SpecialDOC',
          '2020-05-21 10:58:10',
          '최근 토론',
          '___SPECIAL___',
          '최근 진행된 토론들의 목록입니다..',
          '2020-05-30 12:41:08',
          'AUTO',
          '0',
          'none',
          'discussRecent'
        ),(
          -5,
          'SpecialDOC',
          '2020-05-26 07:30:38',
          '임의 문서로',
          '___SPECIAL___',
          '이동중입니다..',
          '2020-05-26 07:30:38',
          'AUTO',
          '0',
          'none',
          'random'
        ),(
          -3,
          'SpecialDOC',
          '2020-08-19 21:22:50',
          '오래된 문서 목록',
          '___SPECIAL___',
          '마지막 편집 역순으로 정렬되며, 최대 50개씩 표시됩니다.',
          '2020-08-19 21:56:00',
          '0',
          '921',
          'none',
          'articles'
        ),(
          -2,
          'SpecialDOC',
          '2020-08-28 17:03:57',
          '부실한 문서 목록',
          '___SPECIAL___',
          '글자 수 순으로 정렬되며, 최대 50개씩 표시됩니다. 틀과 분류, 넘겨주기 문서는 제외됩니다.',
          '2020-08-28 17:03:57',
          'AUTO',
          '0',
          'none',
          'insolvent'
        ),(
          -1,
          'SpecialDOC',
          '2020-08-28 17:03:57',
          '분류되지 않은 문서 목록',
          '___SPECIAL___',
          '마지막 편집 역순으로 정렬되며, 최대 50개씩 표시됩니다. 넘겨주기 문서는 제외됩니다.',
          '2020-08-28 17:03:57',
          'AUTO',
          '0',
          'none',
          'unctgrzd'
        ),(
          0,
          'SpecialDOC',
          '2020-08-28 17:03:57',
          '모든 문서 목록',
          '___SPECIAL___',
          '가나다순으로 정렬되며, 최대 50개씩 표시됩니다. 틀과 분류, 넘겨주기 문서는 제외됩니다.',
          '2020-08-28 17:03:57',
          'AUTO',
          '0',
          'none',
          'abcasc'
        );";
    #}
    /* 게시판 생성 */
    $sql .= "INSERT INTO `_board` (`num`, `id`, `name`, `type`, `at`, `slug`, `title`, `nickTitle`, `boardIntro`, `subs`, `related`, `notice`, `keeper`, `kicked`, `icon`, `option`, `rct`, `tagSet`) VALUES
    (-4, '$P_id', '$P_name', 'AUTO_GENER', NOW(), 'whole', '모든 글 목록', '모든 글 ', '작성된 모든 글이 올라옵니다.', 0, '', NULL, NULL, NULL, NULL, NULL, '1', '기본, 잡담'),
    (-3, '$P_id', '$P_name', 'AUTO_GENER', NOW(), 'fresh', '최근 활동 목록', '목록', '갱신(수정·댓글 작성)된 게시글의 목록입니다.', 0, '', NULL, NULL, NULL, NULL, NULL, 1, '기본, 잡담'),
    (-2, '$P_id', '$P_name', 'AUTO_GENER', NOW(), 'HOF', '명예의 전당', '목록', '커뮤니티에서 지지받는 게시글이 모입니다.', 0, '', NULL, NULL, NULL, NULL, NULL, 1, '기본, 잡담'),
    (-1, '$P_id', '$P_name', 'AUTO_GENER', NOW(), 'trash', '숨겨진 글 목록', '목록', '각 게시판에서 숨김 처리 된 게시글들 입니다.', 0, NULL, '주기적으로 삭제되오니, 잘못 숨겨진 글은 복구 요청을 해주세요.', '', '', NULL, NULL, 1, '기본, 잡담'),
    (0, '$P_id', '$P_name', 'AUTO_GENER', NOW(), 'recent', '종합 글 목록', '종합', '최신순으로 정렬됩니다.', 0, '', NULL, NULL, NULL, 'listing-box', NULL, 1, '기본, 잡담');";
    /* 관리자 계정 생성 */
    $sql .= "INSERT INTO `_account` (`id`, `name`, `type`, `at`, `password`, `mail`, `mailAuth`, `lastIp`, `isAdmin`, `canUpload`, `siteBan`, `autoLogin`, `userAgent`, `userIntro`, `point`)
    VALUES ('$P_id', '$P_name', 'SITE_OWNER', NOW(), '".password_hash($P_password, PASSWORD_BCRYPT)."', '$P_siteEmMail', '1', '127.0.0.1', '1', '1', '0', '0', '', '', '100000000');";
        $sql .= "INSERT INTO `_account` (`id`, `name`, `type`, `at`, `password`, `mail`, `mailAuth`, `lastIp`, `isAdmin`, `canUpload`, `siteBan`, `autoLogin`, `userAgent`, `userIntro`, `point`)
        VALUES ('AUTO', 'AUTO', 'SITE_OWNER', NOW(), '".password_hash($P_password, PASSWORD_BCRYPT)."', 'noreply@fnbase.xyz', '1', '127.0.0.1', '1', '1', '0', '0', '', '자동 생성된 계정입니다.', '-100000000');";
    $sql .= "INSERT INTO `_userSet` (`id`, `name`, `type`, `at`, `fnbcon`) VALUES ('$P_id', '$P_name', 'COMMON', CURRENT_TIMESTAMP, 'default')";
    $result = mysqli_query($conn, $sql);

    /* SQL 쿼리 질의 */
    if (mysqli_multi_query($conn, $sql)) {
        do {
            if ($result = mysqli_store_result($conn)) {
                mysqli_free_result($result);
            }
        } while (mysqli_next_result($conn));
    }

#설정 파일 생성
$myfile = fopen("../setting.php", "w") or die("파일을 열 수 없습니다!");
$file = '<?php
# 이 파일은 FNBase Engine 2의 설정 파일입니다.
$fnVersion = \'2.2\'; #세팅 파일이 작성될 때의 버전입니다.

#데이터베이스 연결 설정입니다.
$fnSiteDB = \''.$P_db.'\'; #데이터베이스 주소
$fnSiteDBuser = \''.$P_dbid.'\'; #데이터베이스 유저
$fnSiteDBpw = \''.$P_dbpw.'\'; #데이터베이스 비밀번호
$fnSiteDBname = \''.$P_dbname.'\'; #기본 데이터베이스 이름
$conn = mysqli_connect("$fnSiteDB", "$fnSiteDBuser", "$fnSiteDBpw", "$fnSiteDBname");


/* 이 아래는 일반적인 경우 수정하지 않으시는게 좋습니다. */
if(!$fnMultiNum){
  $fnMultiNum = 1;
}
$query = "SELECT * from `_setting` WHERE `num` = $fnMultiNum";
$query_result = mysqli_query($conn, $query);
if($query_result !== FALSE){
    while($setting = mysqli_fetch_assoc($query_result)){
        $fnTitle = $setting[\'siteTitle\'];
        $fnDesc = $setting[\'siteDesc\'];
        $fnPath = $setting[\'sitePath\'];
        $fnFab = $setting[\'siteFab\'];
        $fnLang = $setting[\'siteLang\'];
        $fnEmMail = $setting[\'siteEmMail\'];
        $fnPHead = $setting[\'pageHead\'];
        $fnPLeft = $setting[\'pageLeft\'];
        $fnPColor = $setting[\'pageColor\'];
        $fnSColor = $setting[\'pageSubColor\'];
        $fnBColor = $setting[\'pageBgColor\'];
        $fnPFooter = $setting[\'pageFooter\'];
        $fnTz = $setting[\'siteTimezone\'];
        $fnRctHide = $setting[\'recentHide\'];
        $fnType = $setting[\'type\'];
    }
}else{
/* 데이터베이스 연결에 실패할 경우 메시지 출력 */
    die("데이터베이스 연결 실패.");
}

date_default_timezone_set($fnTz);
session_start();
$id = $_SESSION[\'fnUserId\'];
$name = $_SESSION[\'fnUserName\'];
function get_client_ip(){
    if(getenv(\'HTTP_X_FORWARDED_FOR\'))
      $ipaddress = getenv(\'HTTP_X_FORWARDED_FOR\');
    else if(getenv(\'HTTP_X_FORWARDED\'))
      $ipaddress = getenv(\'HTTP_X_FORWARDED\');
    else if(getenv(\'HTTP_FORWARDED_FOR\'))
      $ipaddress = getenv(\'HTTP_FORWARDED_FOR\');
    else if(getenv(\'HTTP_FORWARDED\'))
      $ipaddress = getenv(\'HTTP_FORWARDED\');
    else if(getenv(\'REMOTE_ADDR\'))
      $ipaddress = getenv(\'REMOTE_ADDR\');
    else
      $ipaddress = \'0.4.0.4\';

    return $ipaddress;
}

$ip = get_client_ip();

if($fnType == \'OFF\'){
    if(!$isNot){
        die(\'<script>location.href = "/sub/"</script>\');
    }
}
?>';
fwrite($myfile, $file);
fclose($myfile);
if(file_exists('../setting.php')){
    $complete = TRUE;
}

#완료
if($complete){
    die('커뮤니티 메인 화면으로 이동합니다..<script>location.href="/"</script>');
}
?>
