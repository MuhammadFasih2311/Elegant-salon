<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: admin-login.php");
  exit();
}
include("auth-check.php"); 
include("connect.php");
// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$filter_name  = $_GET['name'] ?? '';
$filter_email = $_GET['email'] ?? '';

$where = " WHERE 1 ";
if ($filter_name) {
  $safe_name = mysqli_real_escape_string($conn, $filter_name);
  $where .= " AND name LIKE '%$safe_name%' ";
}
if ($filter_email) {
  $safe_email = mysqli_real_escape_string($conn, $filter_email);
  $where .= " AND email LIKE '%$safe_email%' ";
}

// Total records with filter
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM contacts $where");
$total_row = mysqli_fetch_assoc($total_query);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch records with filter + pagination
if (!empty($_SESSION['hidden_messages'])) {
  $hidden = implode(",", array_map("intval", $_SESSION['hidden_messages']));
  $where .= " AND id NOT IN ($hidden)";
}
$sql = "SELECT * FROM contacts $where ORDER BY id DESC LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="View and manage customer contact messages sent via the Elegant Salon website. Delete or search through inquiries easily." />
  <title>Contact Messages - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    @media print { .no-print { display: none !important; } }

    .dropdown-menu .dropdown-item:hover {
      background-color: #ffc107;
      color: #000 !important;
    }

    /* Dark Mode Styles */
    body.dark-mode {
      background-color: #121212;
      color: #fff;
    }

    body.dark-mode .table {
      color: #fff;
      background-color: #1a1a1a;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.7);
    }

    body.dark-mode .table thead {
      background-color: #222 !important;
      color: #ffc107 !important;
    }

    body.dark-mode .table thead th {
      background-color: #222 !important;
      color: #ffc107 !important;
      border-bottom: 2px solid #444;
    }

    body.dark-mode .table tbody { background-color: #1a1a1a; color: #fff; }
    body.dark-mode .table-striped tbody tr:nth-of-type(odd) { background-color: #2a2a2a; }
    body.dark-mode .table-hover tbody tr:hover { background-color: #333; }

    body.dark-mode .table-bordered th,
    body.dark-mode .table-bordered td { border-color: #444; }

    body.dark-mode td, body.dark-mode th { color: #000000ff; }

    body.dark-mode .btn-outline-warning {
      color: #ffc107;
      border-color: #ffc107;
    }
    body.dark-mode .btn-outline-warning:hover {
      background-color: #ffc107;
      color: #000;
    }

    body.dark-mode .page-link { background-color: #222; color: #fff; }
    body.dark-mode .page-item.active .page-link {
      background-color: #ffc107; color: #000; border-color: #ffc107;
    }

    body.dark-mode #darkToggle { background-color: #fff !important; color: #000 !important; border: none; }
    #darkToggle { transition: background-color 0.3s ease, color 0.3s ease; }
body.dark-mode .text-dark{
  color:white!important;
}
    /* Table alignment fix */
    .table th, .table td {
      text-align: center;
      vertical-align: middle;
      white-space: nowrap;
    }

    /* Specific for message column (allow wrapping, left align) */
    .table td:nth-child(4) {
      max-width: 400px;
      white-space: normal;
      text-align: left;
      word-break: break-word;
    }

    /* Column width control */
    .table th:nth-child(1), .table td:nth-child(1) { width: 5%; }
    .table th:nth-child(2), .table td:nth-child(2) { width: 15%; }
    .table th:nth-child(3), .table td:nth-child(3) { width: 20%; }
    .table th:nth-child(4), .table td:nth-child(4) { width: 35%; }
    .table th:nth-child(5), .table td:nth-child(5) { width: 15%; }
    .table th:nth-child(6), .table td:nth-child(6) { width: 10%; }

    /* Buttons alignment */
    .table .btn { padding: 3px 8px; font-size: 13px; }

    /* Message truncate fix */
    .table td:nth-child(4) span {
      display: inline-block;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    @media (max-width: 768px) {
      .table td:nth-child(4) span { display: none; /* sirf button dikhega */ }
    }

    /* Backdrop blur + stacking fix */
    .modal-backdrop.show {
      backdrop-filter: blur(8px);
      background-color: rgba(0,0,0,0.55);
    }
    .modal-backdrop { z-index: 1050 !important; }
    .modal { z-index: 1060 !important; }

    /* Modal adaptive styles (dark/light handled via JS class toggle below) */
    .modal-content.custom-dark {
      background: #1e1e1e;
      color: #fff;
      border-radius: 14px;
      border: 1px solid rgba(255,215,0,0.18);
      box-shadow: 0 10px 40px rgba(0,0,0,0.6);
    }
    .modal-content.custom-light {
      background: #fff;
      color: #000;
      border-radius: 14px;
      border: 1px solid rgba(0,0,0,0.08);
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .modal-header .modal-title { font-weight: 600; }
    .modal-body p { line-height: 1.6; margin-bottom: 0.6rem; }
    .modal-body hr { margin: 0.6rem 0; opacity: 0.12; }
    body.modal-open { overflow: auto !important; }
  
.modal-content.custom-dark {
  background: #1e1e1e;
  color: #fff;
  border-radius: 14px;
  border: 1px solid rgba(255,215,0,0.18);
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.modal-content.custom-light {
  background: #fff;
  color: #000;
  border-radius: 14px;
  border: 1px solid rgba(0,0,0,0.08);
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

  </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include("navbar.php"); ?>
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow z-3" id="darkToggle" title="Toggle Dark Mode">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<div class="container my-5 flex-grow-1">
  <div class="d-flex justify-content-center align-items-center mb-4">
    <h2 class="text-warning" data-aos="fade-down">📨 Contact Messages</h2>
  </div>

  <!-- Filter form -->
  <form method="get" class="row g-3 align-items-end mb-3">
    <div class="col-md-4">
      <label class="form-label" data-aos='fade-right' data-aos-delay="100">Filter by Name</label>
      <input type="text" name="name" class="form-control"
             value="<?= htmlspecialchars($filter_name) ?>"
             maxlength="30" pattern="[A-Za-z\s]{1,30}"
             title="Only alphabets allowed, max 30 letters" data-aos='fade-right'>
    </div>
    <div class="col-md-4">
      <label class="form-label" data-aos='fade-left' data-aos-delay="100">Filter by Email</label>
      <input type="email" name="email" class="form-control"
             value="<?= htmlspecialchars($filter_email) ?>"
             maxlength="40" data-aos='fade-left'>
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn btn-primary" data-aos='fade-left' data-aos-delay="100">Apply Filters</button>
      <a href="admin-messages.php" class="btn btn-secondary" data-aos='fade-left' data-aos-delay="200">Reset</a>
    </div>
  </form>

  <div class="table-responsive" data-aos="fade-up">
    <table class="table table-bordered table-hover table-striped align-middle text-center">
      <thead class="table-dark">
  <tr>
    <th>#</th>
    <th>Name</th>
    <th>Email</th>
    <th>Message</th>
    <th>Sent At</th>
    <th>Status</th>
    <th class="no-print">Actions</th>
  </tr>
</thead>
      <tbody>
<?php 
$count = 1; 
$delay = 0; 
while ($row = mysqli_fetch_assoc($result)):
  $delay += 100;
  $js_id = (int)$row['id'];
  $js_name = json_encode($row['name']);
  $js_email = json_encode($row['email']);
  $js_message = json_encode($row['message']);
  $js_reply = json_encode($row['reply']);
  $status_badge = $row['reply'] 
      ? "<span class='badge bg-success'>Replied</span>" 
      : "<span class='badge bg-danger'>Not Replied</span>";
?>
<tr data-aos="fade-right" data-aos-delay="<?= $delay ?>">
  <td><?= $count++ ?></td>
  <td><?= htmlspecialchars($row['name']) ?></td>
  <td><?= htmlspecialchars($row['email']) ?></td>
  <td>
    <div class="d-flex flex-column align-items-start">
      <span class="text-truncate" style="max-width:250px;">
        <?= htmlspecialchars(mb_strimwidth($row['message'], 0, 40, "...")) ?>
      </span>
      <button type="button"
              class="btn btn-sm btn-info mt-2"
              onclick='openMessageModal(<?= $js_id ?>, <?= $js_name ?>, <?= $js_email ?>, <?= $js_message ?>, <?= $js_reply ?>)'>
        View
      </button>
    </div>
  </td>
  <td><?= date("d M Y, h:i A", strtotime($row['created_at'] ?? '')) ?></td>
  <td><?= $status_badge ?></td>
  <td class="no-print">
    <button class="btn btn-sm btn-danger" onclick="removeRow(this)">
  Delete
</button>

  </td>
</tr>
<?php endwhile; ?>
</tbody>
    </table>
  </div>

  <!-- Pagination -->
  <nav class="mt-4" data-aos="fade-up">
    <ul class="pagination justify-content-center">
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?name=<?= urlencode($filter_name) ?>&email=<?= urlencode($filter_email) ?>&page=<?= $page - 1 ?>">Previous</a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="?name=<?= urlencode($filter_name) ?>&email=<?= urlencode($filter_email) ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <li class="page-item">
          <a class="page-link" href="?name=<?= urlencode($filter_name) ?>&email=<?= urlencode($filter_email) ?>&page=<?= $page + 1 ?>">Next</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</div>

<?php include("foot.php"); ?>

<!-- ✨ BEAUTIFULLY STYLED MESSAGE MODAL -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 550px;">
    <div class="modal-content border-0 shadow-lg" id="messageModalContent">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold text-warning" id="messageModalTitle">📩 Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-3 pb-2" id="messageModalBody">
        <!-- Content injected by JS -->
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap + AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
  AOS.init({ duration: 1000});
document.addEventListener("DOMContentLoaded", function () {
  const darkToggle = document.getElementById("darkToggle");
  const currentMode = localStorage.getItem("darkMode");
  if (currentMode === "enabled") document.body.classList.add("dark-mode");

  darkToggle?.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");
    localStorage.setItem("darkMode", document.body.classList.contains("dark-mode") ? "enabled" : "disabled");
    applyModalTheme();
  });

  function applyModalTheme() {
    const modal = document.getElementById("messageModalContent");
    if (!modal) return;
    const dark = document.body.classList.contains("dark-mode");
    modal.classList.toggle("custom-dark", dark);
    modal.classList.toggle("custom-light", !dark);
  }
  applyModalTheme();

  // Utility functions
  function escapeHtml(str) {
    return String(str || "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }
  function nl2br(str) {
    return String(str).replace(/\r\n|\n\r|\r|\n/g, "<br>");
  }

  // Main function to open modal
window.openMessageModal = function (id, name, email, message, reply = "") {
  applyModalTheme();
  const body = document.getElementById("messageModalBody");
  const title = document.getElementById("messageModalTitle");
  title.textContent = `📩 Message from ${name}`;

  const hasReply = reply && reply.trim() !== "";
  const replyBox = hasReply
    ? `<div id="replyDisplay" class="border rounded p-2 bg-light-subtle">${nl2br(escapeHtml(reply))}</div>`
    : `<textarea id="replyText" class="form-control form-control-sm" rows="3" placeholder="Write your reply..."></textarea>`;

  const buttons = hasReply
    ? `<button class="btn btn-warning btn-sm px-3 me-2" id="editReply"><i class="bi bi-pencil"></i> Edit Reply</button>`
    : `<button class="btn btn-success btn-sm px-3 me-2" id="saveReply"><i class="bi bi-send"></i> Save Reply</button>`;

  body.innerHTML = `
    <div class="p-2">
      <div class="mb-2">
        <small class="text-dark fw-bold">Email:</small>
        <div class="border rounded px-2 py-1 bg-light-subtle">${escapeHtml(email)}</div>
      </div>
      <div class="mb-2">
        <small class="text-dark fw-bold">Message:</small>
        <div class="border rounded px-2 py-2 bg-light-subtle">${nl2br(escapeHtml(message))}</div>
      </div>
      <div class="mb-2">
        <small class="text-dark fw-bold">Reply:</small>
        <div id="replyArea">${replyBox}</div>
      </div>
      <div class="d-flex justify-content-end mt-3">
        ${buttons}
        <button class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  `;

  const modal = new bootstrap.Modal(document.getElementById("messageModal"));
  modal.show();

  // Save new reply
  const saveReplyBtn = document.getElementById("saveReply");
  if (saveReplyBtn) {
    saveReplyBtn.addEventListener("click", () => {
      const replyText = document.getElementById("replyText").value.trim();
      if (!replyText) return alert("Please write a reply!");

      fetch("save-reply.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&reply=${encodeURIComponent(replyText)}`
      })
      .then(res => res.text())
      .then(data => {
        if (data.trim() === "done") {
          alert("✅ Reply saved successfully!");
          modal.hide();
          location.reload();
        } else alert("❌ Error saving reply!");
      });
    });
  }

  // Edit existing reply
  const editBtn = document.getElementById("editReply");
  if (editBtn) {
    editBtn.addEventListener("click", () => {
      document.getElementById("replyArea").innerHTML = `
        <textarea id="replyText" class="form-control form-control-sm mb-2" rows="3">${escapeHtml(reply)}</textarea>
        <button class="btn btn-success btn-sm px-3" id="updateReply"><i class="bi bi-save"></i> Save Changes</button>
      `;
      editBtn.style.display = "none";

      document.getElementById("updateReply").addEventListener("click", () => {
        const updatedReply = document.getElementById("replyText").value.trim();
        if (!updatedReply) return alert("Please enter reply text!");
        fetch("save-reply.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${id}&reply=${encodeURIComponent(updatedReply)}`
        })
        .then(res => res.text())
        .then(data => {
          if (data.trim() === "done") {
            alert("✅ Reply updated!");
            modal.hide();
            location.reload();
          } else alert("❌ Error updating reply!");
        });
      });
    });
  }
};
});
function removeRow(btn) {
  if (confirm("Are you sure you want to remove this message from view?")) {
    const row = btn.closest("tr");
    const messageId = row.querySelector("button.btn-info").getAttribute("onclick").match(/\((\d+),/)[1];

    row.style.transition = "opacity 0.4s ease";
    row.style.opacity = "0";
    setTimeout(() => row.remove(), 400);

    fetch("hide-message.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${messageId}`
    });
  }
}

</script>
</body>
</html>
