<?php
header("Content-Type: application/json");
require_once 'connection.php'; // ✅ include your DB connection ($conn)

// ====== Define base image path ======


// ====== Allow only POST ======
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => false, "message" => "Only POST method allowed"]);
    exit();
}

// ====== Input ======
$searchValue   = isset($_POST['search']) ? trim($_POST['search']) : '';
$current_page  = isset($_POST['current_page']) ? (int)$_POST['current_page'] : 1;
$per_page      = isset($_POST['per_page']) ? (int)$_POST['per_page'] : 10;

$current_page = max(1, $current_page);
$per_page     = max(1, $per_page);

$hasSearch = !empty($searchValue);

try {
    // ===== Base Query =====
    $sql = "SELECT id, title, descriptions, tags, photos, owner, created_at 
<<<<<<< HEAD
            FROM feeds 
=======
            FROM feeds f
            INNER JOIN new_users u ON u.id = f.owner
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
            WHERE deleted_at IS NULL";
    $params = [];

    // ===== Search Filter =====
    if ($hasSearch) {
        $sql .= " AND (
            LOWER(title) LIKE :search OR 
            LOWER(descriptions) LIKE :search OR 
            LOWER(tags) LIKE :search OR 
            LOWER(photos) LIKE :search
        )";
        $params[':search'] = "%" . strtolower($searchValue) . "%";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== Function: Calculate Match Score =====
    function calculateMatchScore($post, $fields, $searchValue) {
        $score = 0;
        $searchValue = strtolower($searchValue);
        $searchWords = preg_split('/\s+/', $searchValue);

        foreach ($fields as $field) {
            $value = $post;
            foreach (explode('.', $field) as $k) {
                $value = isset($value[$k]) ? $value[$k] : null;
            }
            if ($value) {
                if (is_array($value)) {
                    $value = implode(' ', $value); // flatten arrays
                }
                $value = strtolower($value);

                if (strpos($value, $searchValue) !== false) {
                    $score += 100;
                    continue;
                }

                $foundWords = 0;
                foreach ($searchWords as $word) {
                    if (strpos($value, $word) !== false) {
                        $foundWords++;
                    }
                }
                if ($foundWords > 0) {
                    $score += intval(50 * ($foundWords / count($searchWords)));
                }
            }
        }
        return $score;
    }

    // ===== Relations + Image Paths =====
    foreach ($posts as &$post) {
        // ✅ Fix photos
        if (!empty($post['photos'])) {
            $photos = json_decode($post['photos'], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($photos)) {
                $post['photos'] = array_map(function ($p) {
                    return $GLOBALS['dealimage'] . ltrim($p);
                }, $photos);
            } else {
                $photos = explode(',', $post['photos']);
                $post['photos'] = array_map(function ($p) {
                    return $GLOBALS['dealownerimagepath'] . ltrim(trim($p, '[]"'));
                }, $photos);
            }
        } else {
            $post['photos'] = [];
        }

        // ✅ Owner Info
        if (!empty($post['owner'])) {
            $ownerStmt = $conn->prepare("SELECT id, first_name, last_name, email, city, image 
<<<<<<< HEAD
                                         FROM users WHERE id = ?");
=======
                                         FROM new_users WHERE id = ?");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
            $ownerStmt->execute([$post['owner']]);
            $post['ownerInfo'] = $ownerStmt->fetch(PDO::FETCH_ASSOC);

            if ($post['ownerInfo']) {
                if (!empty($post['ownerInfo']['image'])) {
                    $post['ownerInfo']['image'] = $GLOBALS['dealownerimagepath'] . ltrim(trim($post['ownerInfo']['image'], '[]"'));
                } else {
                    $post['ownerInfo']['image'] = null;
                }

                // ✅ City Info
                if (!empty($post['ownerInfo']['city'])) {
                    $cityStmt = $conn->prepare("SELECT id, name FROM citys WHERE id = ?");
                    $cityStmt->execute([$post['ownerInfo']['city']]);
                    $post['ownerInfo']['usercity'] = $cityStmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $post['ownerInfo']['usercity'] = null;
                }

                // ✅ Boost Info
                $boostStmt = $conn->prepare("SELECT id, user_id, keywords 
                                             FROM boosts WHERE user_id = ?");
                $boostStmt->execute([$post['ownerInfo']['id']]);
                $post['boost'] = $boostStmt->fetch(PDO::FETCH_ASSOC);
            }
        } else {
            $post['ownerInfo'] = null;
            $post['boost'] = null;
        }

        // ✅ Accuracy Score
        $post['accuracy_score'] = $hasSearch
            ? calculateMatchScore($post, ['title', 'descriptions', 'tags', 'photos'], $searchValue)
            : 0;
    }

    // ===== Sorting =====
    if ($hasSearch) {
        $posts = array_filter($posts, fn($p) => $p['accuracy_score'] > 0);
        usort($posts, fn($a, $b) => $b['accuracy_score'] <=> $a['accuracy_score']);
    } else {
        usort($posts, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
    }

    // ===== Pagination =====
    $total_results = count($posts);
    $last_page     = (int) ceil($total_results / $per_page);
    $offset        = ($current_page - 1) * $per_page;
    $pagedPosts    = array_slice($posts, $offset, $per_page);

    $from = $total_results > 0 ? $offset + 1 : null;
    $to   = $offset + count($pagedPosts);

    // ===== Response =====
    echo json_encode([
        "status"        => true,
        "searchValue"   => $searchValue,
        "total_results" => $total_results,
        "per_page"      => $per_page,
        "current_page"  => $current_page,
        "last_page"     => $last_page,
        "from"          => $from,
        "to"            => $to,
        "data"          => $pagedPosts
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
