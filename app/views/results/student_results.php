<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <a href="/my-class" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> Back to Class</a>
            <h3 class="mb-0 fw-bold">Grade Student: <?= htmlspecialchars($student->surname . ' ' . $student->first_name) ?></h3>
            <p class="text-muted mb-0">Reg No: <?= htmlspecialchars($student->registration_number) ?></p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6"><?= $currentSession ? htmlspecialchars($currentSession->name) : '' ?></span>
            <span class="badge bg-secondary fs-6"><?= $currentTerm ? htmlspecialchars($currentTerm->name) : '' ?></span>
        </div>
    </div>
</div>



<?php
// Check lock status
$status = $resultRecord ? $resultRecord->status : 'OPEN';
$is_locked = in_array($status, ['APPROVED', 'PUBLISHED', 'PENDING']);
?>

<?php if (empty($subjects)): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i> No subjects have been added to this class curriculum yet. 
        <a href="/results/subjects" class="alert-link">Manage Subjects</a>
    </div>
<?php else: ?>
    <div class="card shadow-sm border-0 rounded-3">
        <form action="/results/student/<?= $student->id ?>/save" method="post" id="resultForm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" id="resultsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Subject</th>
                                <th style="width: 120px;">CA1 (20)</th>
                                <th style="width: 120px;">CA2 (20)</th>
                                <th style="width: 120px;">Exam (60)</th>
                                <th style="width: 100px;">Total</th>
                                <th style="width: 100px;">Grade</th>
                                <th style="width: 150px;">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($subjects as $subject): 
                                $res = $resultItems[$subject->id] ?? null;
                                $ca1 = $res ? $res->ca1 : '';
                                $ca2 = $res ? $res->ca2 : '';
                                $exam = $res ? $res->exam : '';
                                $total = $res ? $res->total : '';
                                $grade = $res ? $res->grade : '';
                                $remark = $res ? $res->remark : '';
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $i++ ?></td>
                                    <td class="fw-medium text-dark"><?= htmlspecialchars($subject->name) ?></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control form-control-sm ca1-input" style="min-width: 60px;"
                                               name="scores[<?= $subject->id ?>][ca1]" value="<?= htmlspecialchars($ca1) ?>" <?= $is_locked ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control form-control-sm ca2-input" style="min-width: 60px;"
                                               name="scores[<?= $subject->id ?>][ca2]" value="<?= htmlspecialchars($ca2) ?>" <?= $is_locked ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="60" class="form-control form-control-sm exam-input" style="min-width: 60px;"
                                               name="scores[<?= $subject->id ?>][exam]" value="<?= htmlspecialchars($exam) ?>" <?= $is_locked ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm total-input bg-light" style="min-width: 60px;" readonly value="<?= htmlspecialchars($total) ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm grade-input bg-light fw-bold" style="min-width: 60px;" readonly value="<?= htmlspecialchars($grade) ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm remark-input bg-light" style="min-width: 100px;" readonly value="<?= htmlspecialchars($remark) ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-body border-top bg-light">
                <div class="row">
                    <div class="col-12 mb-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="generateAutoComments()" <?= $is_locked ? 'disabled' : '' ?>>
                            <i class="fas fa-magic"></i> Auto-Generate Comments
                        </button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Class Teacher's Remark</label>
                        <textarea name="class_teacher_remark" id="ct_remark" class="form-control" rows="2" placeholder="Teacher's remarks..." <?= $is_locked ? 'readonly' : '' ?>><?= $resultRecord ? htmlspecialchars($resultRecord->class_teacher_remark ?? '') : '' ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Head Teacher's Comment</label>
                        <textarea name="head_teacher_remark" id="ht_remark" class="form-control" rows="2" placeholder="Head Teacher's remarks..." <?= $is_locked ? 'readonly' : '' ?>><?= $resultRecord ? htmlspecialchars($resultRecord->head_teacher_remark ?? '') : '' ?></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Head Teacher's Name</label>
                        <input type="text" name="head_teacher_name" class="form-control" placeholder="E.g. SADIQ SABO ABBA" value="<?= $resultRecord ? htmlspecialchars($resultRecord->head_teacher_name ?? '') : '' ?>" <?= $is_locked ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Attendance in Class</label>
                        <input type="number" name="attendance" class="form-control" placeholder="E.g. 99" value="<?= $resultRecord ? htmlspecialchars($resultRecord->attendance ?? '') : '' ?>" <?= $is_locked ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">School Resumption Date</label>
                        <input type="text" name="resumption_date" class="form-control" placeholder="E.g. 10th Sept 2026" value="<?= $resultRecord ? htmlspecialchars($resultRecord->resumption_date ?? '') : '' ?>" <?= $is_locked ? 'readonly' : '' ?>>
                    </div>
                </div>
            </div>

            <?php if (!$is_locked): ?>
            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-primary px-4 rounded-pill">
                    <i class="fas fa-save me-2"></i> Save Scores
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<!-- Grading System Data for JS -->
<script>
    const gradingSystem = <?= json_encode($gradingSystem) ?>;
</script>

<?php 
$extraJs = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("resultsTable");
    if (!table) return;

    table.addEventListener("input", function(e) {
        if (e.target.classList.contains("ca1-input") || 
            e.target.classList.contains("ca2-input") || 
            e.target.classList.contains("exam-input")) {
            
            const row = e.target.closest("tr");
            calculateRow(row);
        }
    });

    function calculateRow(row) {
        const ca1 = parseFloat(row.querySelector(".ca1-input").value) || 0;
        const ca2 = parseFloat(row.querySelector(".ca2-input").value) || 0;
        const exam = parseFloat(row.querySelector(".exam-input").value) || 0;
        
        let c1 = Math.min(Math.max(ca1, 0), 20);
        let c2 = Math.min(Math.max(ca2, 0), 20);
        let ex = Math.min(Math.max(exam, 0), 60);

        const total = c1 + c2 + ex;
        
        row.querySelector(".total-input").value = total > 0 ? total.toFixed(2) : "";

        let grade = "";
        let remark = "";
        let colorClass = "";

        if (total > 0 || row.querySelector(".exam-input").value !== "") {
            for (let i = 0; i < gradingSystem.length; i++) {
                const g = gradingSystem[i];
                if (total >= parseFloat(g.min_score) && total <= parseFloat(g.max_score)) {
                    grade = g.grade;
                    remark = g.remark;
                    if (grade.includes("F")) colorClass = "text-danger";
                    else if (grade.includes("A")) colorClass = "text-success";
                    else colorClass = "text-dark";
                    break;
                }
            }
        }

        const gradeInput = row.querySelector(".grade-input");
        gradeInput.value = grade;
        gradeInput.className = "form-control form-control-sm grade-input bg-light fw-bold " + colorClass;
        
        row.querySelector(".remark-input").value = remark;
    }
});

function generateAutoComments() {
    const totals = Array.from(document.querySelectorAll(".total-input"))
                        .map(inp => parseFloat(inp.value))
                        .filter(val => !isNaN(val));
    
    if (totals.length === 0) {
        Swal.fire("Notice", "Please enter some scores first!", "info");
        return;
    }
    
    const avg = totals.reduce((a, b) => a + b, 0) / totals.length;
    
    let ct_remark = "";
    let ht_remark = "";
    
    if (avg >= 80) {
        ct_remark = "An outstanding performance. Keep it up!";
        ht_remark = "Excellent result. I am very proud of your hard work.";
    } else if (avg >= 70) {
        ct_remark = "A very good result. You can do even better.";
        ht_remark = "Very good performance. Keep aiming higher.";
    } else if (avg >= 60) {
        ct_remark = "Good result, but there is room for improvement.";
        ht_remark = "Good effort. Work harder next term.";
    } else if (avg >= 50) {
        ct_remark = "A fair performance. You need to sit up.";
        ht_remark = "Fair result. More dedication to your studies is required.";
    } else if (avg >= 40) {
        ct_remark = "Poor performance. You must work much harder.";
        ht_remark = "Weak result. You need to be more serious with your studies.";
    } else {
        ct_remark = "Very poor result. Please see me.";
        ht_remark = "Unacceptable performance. Parents are advised to monitor the child closely at home.";
    }
    
    document.getElementById("ct_remark").value = ct_remark;
    document.getElementById("ht_remark").value = ht_remark;
}
</script>
';
?>
