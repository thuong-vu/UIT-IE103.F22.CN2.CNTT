<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('employer');

$user    = getCurrentUser();
$epId    = $user['profile_id'];
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Handle delete (soft close) or hard delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $jobId  = (int)($_POST['job_id'] ?? 0);

    try {
        $pdo = getDB();

        // Verify ownership
        $own = $pdo->prepare('SELECT id FROM job_posts WHERE id = ? AND employer_id = ?');
        $own->execute([$jobId, $epId]);
        if (!$own->fetch()) {
            setFlash('danger', 'Post not found or access denied.');
        } elseif ($action === 'close') {
            $pdo->prepare("UPDATE job_posts SET status = 'closed' WHERE id = ?")
                ->execute([$jobId]);
            setFlash('success', 'Job post closed.');
        } elseif ($action === 'open') {
            $pdo->prepare("UPDATE job_posts SET status = 'open' WHERE id = ?")
                ->execute([$jobId]);
            setFlash('success', 'Job post re-opened.');
        } elseif ($action === 'delete') {
            $pdo->prepare('DELETE FROM job_posts WHERE id = ?')->execute([$jobId]);
            setFlash('success', 'Job post deleted.');
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        setFlash('danger', 'Action failed. Please try again.');
    }

    header('Location: /website/employer/manage_posts.php');
    exit;
}

try {
    $pdo = getDB();

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM job_posts WHERE employer_id = ?');
    $countStmt->execute([$epId]);
    $total = (int)$countStmt->fetchColumn();
    $pages = (int)ceil($total / $perPage);

    $stmt = $pdo->prepare(
        'SELECT jp.id, jp.title, jp.location, jp.category, jp.status, jp.created_at,
                (SELECT COUNT(*) FROM applications WHERE job_id = jp.id) AS app_count
         FROM job_posts jp
         WHERE jp.employer_id = ?
         ORDER BY jp.created_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute([$epId, $perPage, $offset]);
    $posts = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $posts = [];
    $total = $pages = 0;
}

$pageTitle = 'Manage Job Posts';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">
    <i class="bi bi-list-ul me-2 text-primary"></i>Manage Job Posts
    <small class="text-muted fs-6">(<?= $total ?> total)</small>
  </h4>
  <a href="/website/employer/post_job.php" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i>Post a Job
  </a>
</div>

<?php if (empty($posts)): ?>
  <div class="text-center py-5 text-muted">
    <i class="bi bi-briefcase fs-1 d-block mb-3"></i>
    You haven't posted any jobs yet.
    <a href="/website/employer/post_job.php">Post your first job</a>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Location</th>
            <th class="text-center">Apps</th>
            <th class="text-center">Status</th>
            <th>Posted</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
            <tr>
              <td>
                <a href="/website/employer/edit_post.php?id=<?= $post['id'] ?>"
                   class="fw-semibold text-decoration-none">
                  <?= htmlspecialchars($post['title']) ?>
                </a>
              </td>
              <td><?= htmlspecialchars($post['category'] ?: '—') ?></td>
              <td><?= htmlspecialchars($post['location'] ?: '—') ?></td>
              <td class="text-center">
                <a href="/website/employer/applicants.php?job_id=<?= $post['id'] ?>"
                   class="badge bg-primary text-decoration-none">
                  <?= $post['app_count'] ?>
                </a>
              </td>
              <td class="text-center">
                <span class="badge bg-<?= $post['status'] === 'open' ? 'success' : 'secondary' ?>">
                  <?= $post['status'] ?>
                </span>
              </td>
              <td class="text-nowrap">
                <?= date('M j, Y', strtotime($post['created_at'])) ?>
              </td>
              <td class="text-end text-nowrap">
                <a href="/website/employer/edit_post.php?id=<?= $post['id'] ?>"
                   class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="/website/employer/applicants.php?job_id=<?= $post['id'] ?>"
                   class="btn btn-sm btn-outline-info ms-1">
                  <i class="bi bi-people"></i>
                </a>
                <form method="post" class="d-inline ms-1">
                  <input type="hidden" name="job_id" value="<?= $post['id'] ?>">
                  <?php if ($post['status'] === 'open'): ?>
                    <button type="submit" name="action" value="close"
                            class="btn btn-sm btn-outline-warning"
                            title="Close posting">
                      <i class="bi bi-pause-circle"></i>
                    </button>
                  <?php else: ?>
                    <button type="submit" name="action" value="open"
                            class="btn btn-sm btn-outline-success"
                            title="Re-open posting">
                      <i class="bi bi-play-circle"></i>
                    </button>
                  <?php endif; ?>
                </form>
                <form method="post" class="d-inline ms-1"
                      onsubmit="return confirm('Permanently delete this post?')">
                  <input type="hidden" name="job_id"  value="<?= $post['id'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
