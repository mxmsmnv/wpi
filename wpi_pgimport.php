<?php

/* 123 */

// Загрузка WordPress
require_once('wp-load.php');

// Параметры для подключения к базе данных PostgreSQL
$dbConfig = [
	'host' => 'db.yboqexgbqpxuoqsywufz.supabase.co',
	'db' => 'postgres',
	'user' => 'postgres',
	'pass' => '',
	'table' => 'italy',
	'options' => [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
	]
];

// Функция для импорта данных из PostgreSQL и создания постов в WordPress
function import_posts_from_postgres($dbConfig) {
	$current_date = current_time('mysql');
	$post_date = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($current_date)));

	try {
		$pdo = new PDO("pgsql:host={$dbConfig['host']};dbname={$dbConfig['db']}", $dbConfig['user'], $dbConfig['pass'], $dbConfig['options']);

		// Получаем все ID, которые имеют статус '0'
		$ids_result = $pdo->query("SELECT id FROM {$dbConfig['table']} WHERE status = '0'");
		$ids = $ids_result->fetchAll(PDO::FETCH_COLUMN, 0);

		// Перемешиваем ID случайным образом
		shuffle($ids);

		// Перебираем все ID и создаем посты
		foreach ($ids as $id) {
			$stmt = $pdo->prepare("SELECT title, intro, text, keyword FROM {$dbConfig['table']} WHERE id = ?");
			$stmt->execute([$id]);
			$row = $stmt->fetch();

			if ($row) { // Если есть данные для этого ID
				$post = [
					'post_title'    => $row['title'],
					'post_excerpt'  => $row['intro'],
					'post_content'  => $row['text'],
					'post_status'   => 'future',
				    'post_author'   => rand(1, 6),
				    'tags_input'    => $row['keyword'],
					'post_format'   => 'standard',
					'post_date'     => $post_date
				];

				$post_id = wp_insert_post($post);

				if ($post_id) {
					echo "Пост успешно создан. ID: " . $post_id . "<br>";
				} else {
					echo "Ошибка при создании поста." . "<br>";
				}

				$post_date = date('Y-m-d H:i:s', strtotime('+12412 minutes', strtotime($post_date)));
				
			/*
				1412 = 1 days
				2412 = 1.68 days
				2412 = 2.3 days
				4412 = 3 days
				12412 = 8.6 days
			*/
			
			}
		}
	} catch (PDOException $e) {
		echo "Ошибка при подключении к базе данных: " . $e->getMessage();
	}
}

// Вызываем функцию импорта постов
import_posts_from_postgres($dbConfig);

?>
