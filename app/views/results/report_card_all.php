<?php
$brandColor = '#0955ac';
$brandColorLight = '#e6f0fa';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print All Report Cards</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; background: #f4f6f9; }
        .container { width: 100%; max-width: 760px; margin: 40px auto; position: relative; background: #fff; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-break { page-break-after: always; margin-bottom: 50px; }
        
        /* Header Section */
        .header-table { width: 100%; margin-bottom: 15px; text-align: center; }
        .header-table td { vertical-align: middle; }
        .school-name { color: <?= $brandColor ?>; font-size: 20px; font-weight: 800; margin-bottom: 5px; text-transform: uppercase; line-height: 1.1; }
        .school-address { font-size: 10px; color: #333; margin-bottom: 3px; }
        .school-contact { font-size: 9px; color: #333; }
        .report-title { font-size: 14px; font-weight: bold; text-align: center; margin: 10px 0 10px 0; color: #111; }
        
        hr { border: none; border-top: 2px solid <?= $brandColor ?>; margin-bottom: 15px; }

        /* Student Info Grid */
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 10px; gap: 5px; }
        .info-col { flex: 1; display: flex; flex-direction: column; gap: 5px; min-width: 0; }
        .info-label { font-weight: 600; color: #000; }
        
        .fee-box { border: 1px solid <?= $brandColor ?>; border-radius: 4px; padding: 0; width: 160px; flex-shrink: 0; }
        .fee-box-title { background: <?= $brandColorLight ?>; color: <?= $brandColor ?>; font-weight: bold; padding: 3px 6px; font-size: 9px; border-bottom: 1px solid <?= $brandColor ?>; }
        .fee-box-content { padding: 4px 6px; font-size: 8px; line-height: 1.4; }
        .fee-box-content .total { font-weight: bold; color: <?= $brandColor ?>; }
        
        .passport-box { width: 70px; height: 75px; border: 1px solid #ccc; background: #eee; display: flex; align-items: center; justify-content: center; margin-left: 8px; flex-shrink: 0;}
        .passport-box img { width: 100%; height: 100%; object-fit: cover; }

        /* General Table Styles */
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9px; table-layout: fixed; word-wrap: break-word; }
        th, td { border: 1px solid #ddd; padding: 4px 2px; text-align: center; }
        th { background-color: <?= $brandColor ?>; color: #fff; font-weight: 600; padding: 5px 2px; }
        
        .text-left { text-align: left; }
        .section-title { font-size: 12px; font-weight: bold; color: <?= $brandColor ?>; margin-bottom: 4px; }

        /* Specific Tables */
        .summary-table td { font-weight: 600; color: #333; }
        .summary-table td:nth-child(odd) { background-color: #f9f9f9; text-align: left; padding-left: 5px; }
        
        .grade-analysis td { font-weight: 600; }
        .grade-scale { font-size: 8px; line-height: 1.3; color: #555; }

        /* Remarks Section */
        .remarks-section { margin-top: 10px; display: flex; gap: 15px; }
        .remark-box { flex: 1; border: 1px solid #ddd; padding: 8px; border-radius: 4px; }
        .remark-title { font-size: 10px; font-weight: bold; color: <?= $brandColor ?>; margin-bottom: 4px; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        .remark-content { font-size: 9px; line-height: 1.4; color: #333; font-style: italic; }
        .resume-date { color: #d32f2f; font-weight: bold; font-size: 9px; margin-top: 8px; }

        /* Print Styles */
        @media print {
            @page { size: A4 portrait; margin: 10mm; }
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; background: #fff !important; }
            .container { width: 190mm !important; max-width: 190mm !important; margin: 20mm auto 0 auto !important; box-shadow: none !important; padding: 0 !important; }
            th { -webkit-print-color-adjust: exact; color-adjust: exact; background-color: <?= $brandColor ?> !important; color: #fff !important; }
            .fee-box-title { -webkit-print-color-adjust: exact; background-color: <?= $brandColorLight ?> !important; }
            .summary-table td:nth-child(odd) { -webkit-print-color-adjust: exact; background-color: #f9f9f9 !important; }
        }
        
        .no-print { text-align: right; margin-bottom: 20px; max-width: 760px; margin: 0 auto 20px auto; }
        .no-print button, .no-print a { padding: 8px 15px; background: #007bff; color: #fff; text-decoration: none; border: none; cursor: pointer; border-radius: 3px; font-size: 12px; }
        .no-print a { background: #6c757d; margin-left: 5px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print All Results</button>
        <a href="/my-class">Close</a>
    </div>

    <?php 
    $count = count($allReports);
    foreach ($allReports as $index => $report):
        extract($report);
    ?>
    <div class="container <?= ($index < $count - 1) ? 'page-break' : '' ?>">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td width="120" style="text-align: left;">
                    <img src="/assets/images/school_logo.jpg" alt="School Logo" style="width: 100px; height: auto;">
                </td>
                <td>
                    <div class="school-name">ISMAIL AHMAD MEMORIAL ACADEMY</div>
                    <div class="school-address">AGENCY FOR MASS EDUCATION WOMEN CENTRE NO.428 TSOHON LAYI JAHUN, JIGAWA STATE</div>
                    <div class="school-contact">
                        Tel: 08036644211, 09078103435, 07038313471 | Email: ismailahmadmemorialschool@yahoo.com
                    </div>
                </td>
                <td width="120"></td>
            </tr>
        </table>

        <div class="report-title"><?= $currentTerm ? htmlspecialchars($currentTerm->name) : '' ?> Student's Performance Report</div>
        <hr>

        <!-- Student Info -->
        <div class="info-grid">
            <div class="info-col">
                <div><span class="info-label">Name:</span> <?= htmlspecialchars($student->surname . ' ' . $student->first_name . ' ' . $student->middle_name) ?></div>
                <div><span class="info-label">Class:</span> <?= htmlspecialchars(strtoupper($class->name)) ?></div>
                <div><span class="info-label">Session:</span> <?= $currentSession ? htmlspecialchars($currentSession->name) : '' ?></div>
                <div><span class="info-label">Term:</span> <?= $currentTerm ? htmlspecialchars($currentTerm->name) : '' ?></div>
            </div>
            <div class="info-col">
                <div><span class="info-label">Gender:</span> <?= htmlspecialchars(ucfirst($student->gender)) ?></div>
                <div><span class="info-label">Admission No:</span> <?= htmlspecialchars($student->registration_number) ?></div>
                <div><span class="info-label">D.O.B:</span> <?= htmlspecialchars($student->dob ?? 'N/A') ?></div>
                <div><span class="info-label">Parent(s):</span> <?= htmlspecialchars($student->parent_name ?? 'Guardian') ?></div>
            </div>
            <div style="display: flex;">
                <div class="fee-box">
                    <?php 
                        $nextTermName = '';
                        if ($currentTerm) {
                            if (stripos($currentTerm->name, 'First') !== false) $nextTermName = 'Second Term';
                            elseif (stripos($currentTerm->name, 'Second') !== false) $nextTermName = 'Third Term';
                            elseif (stripos($currentTerm->name, 'Third') !== false) $nextTermName = 'First Term';
                            else $nextTermName = 'Next Term';
                        }
                    ?>
                    <div class="fee-box-title">Next Term Fee (<?= htmlspecialchars($nextTermName) ?>)</div>
                    <div class="fee-box-content">
                        <?php 
                            // Past balance and next fee are now passed automatically from the Finance Controller logic
                            $past = isset($pastBalance) ? (float)$pastBalance : 0;
                            $next = isset($nextFee) ? (float)$nextFee : 0;
                            $totalPayable = $past + $next;
                        ?>
                        <div>Past Balance: ₦<?= number_format($past, 2) ?></div>
                        <div>Next Term Bill: ₦<?= number_format($next, 2) ?></div>
                        <div class="total">Total Payable: ₦<?= number_format($totalPayable, 2) ?></div>
                        <div style="font-size: 8px; color: #888; font-style: italic; margin-top: 3px;">* This is a summary of expected fees.</div>
                    </div>
                </div>
                <div class="passport-box">
                    <svg viewBox="0 0 24 24" fill="#d1d5db" style="width: 80%; height: 80%;">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="section-title">Cognitive Domain</div>
        <table>
            <thead>
                <tr>
                    <th class="text-left" style="width: 30%;">Subjects</th>
                    <th>CA 1</th>
                    <th>CA 2</th>
                    <th>Exams</th>
                    <th>Total</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sum_ca = 0;
                $sum_exam = 0;
                $gradeCounts = ['A1'=>0, 'B2'=>0, 'B3'=>0, 'C4'=>0, 'C5'=>0, 'C6'=>0, 'D7'=>0, 'E8'=>0, 'F9'=>0];
                
                foreach ($items as $item): 
                    $ca = $item->ca1 + $item->ca2;
                    $sum_ca += $ca;
                    $sum_exam += $item->exam;
                    
                    $gText = strtoupper(trim($item->grade));
                    foreach(array_keys($gradeCounts) as $gk) {
                        if (strpos($gText, $gk) !== false || $gText == $gk) {
                            $gradeCounts[$gk]++;
                        }
                    }
                ?>
                    <tr>
                        <td class="text-left"><?= htmlspecialchars($item->subject_name) ?></td>
                        <td><?= htmlspecialchars($item->ca1) ?></td>
                        <td><?= htmlspecialchars($item->ca2) ?></td>
                        <td><?= htmlspecialchars($item->exam) ?></td>
                        <td><?= htmlspecialchars($item->total) ?></td>
                        <td><?= htmlspecialchars($item->grade) ?></td>
                        <td><?= htmlspecialchars($item->remark) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <div class="section-title">Performance Summary</div>
                <table class="summary-table">
                    <tr>
                        <td width="30%">Total Marks Obtained</td>
                        <td width="20%"><?= number_format($totalScore, 0) ?></td>
                        <td width="30%">Average Score</td>
                        <td width="20%"><?= number_format($classAverage, 2) ?></td>
                    </tr>
                    <tr>
                        <td>Total Marks Obtainable</td>
                        <td><?= count($items) * 100 ?></td>
                        <td>Position</td>
                        <td><?= htmlspecialchars($positionStr) ?></td>
                    </tr>
                    <tr>
                        <td>Class Population</td>
                        <td><?= $totalStudents ?></td>
                        <td>Attendance</td>
                        <td><?= $resultRecord ? htmlspecialchars($resultRecord->attendance ?? '') : '-' ?></td>
                    </tr>
                </table>
            </div>
            
            <div style="flex: 1;">
                <div class="section-title">Grade Analysis</div>
                <table class="grade-analysis">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>A1</th><th>B2</th><th>B3</th><th>C4</th><th>C5</th><th>C6</th><th>D7</th><th>E8</th><th>F9</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>No. of Subjects</td>
                            <td><?= $gradeCounts['A1'] ?></td>
                            <td><?= $gradeCounts['B2'] ?></td>
                            <td><?= $gradeCounts['B3'] ?></td>
                            <td><?= $gradeCounts['C4'] ?></td>
                            <td><?= $gradeCounts['C5'] ?></td>
                            <td><?= $gradeCounts['C6'] ?></td>
                            <td><?= $gradeCounts['D7'] ?></td>
                            <td><?= $gradeCounts['E8'] ?></td>
                            <td><?= $gradeCounts['F9'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 20px; align-items: flex-start;">
            <div style="width: 80px; height: 80px; flex-shrink: 0;">
                <?php 
                $verifyData = "Student: " . $student->surname . " " . $student->first_name . " | Reg No: " . $student->registration_number . " | Term: " . ($currentTerm ? $currentTerm->name : '');
                ?>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= rawurlencode($verifyData) ?>" alt="QR Code" style="width: 100%; height: 100%; border: 1px solid #ccc;">
            </div>
            <div>
                <div style="font-weight: bold; color: <?= $brandColor ?>; font-size: 12px; margin-bottom: 5px;">Grade Scale</div>
                <div class="grade-scale">
                    <?php 
                    $scales = [];
                    foreach($gradingSystem as $g) {
                        $scales[] = "{$g->min_score} - {$g->max_score}: {$g->grade} ({$g->remark})";
                    }
                    echo implode(", ", $scales);
                    ?>
                    <br><br>Scan the QR code to Verify
                </div>
            </div>
        </div>

        <!-- Remarks Section -->
        <div class="remarks-section">
            <div class="remark-box">
                <div class="remark-title">Form Master's Remark</div>
                <div class="remark-content">
                    <?= $resultRecord ? htmlspecialchars($resultRecord->class_teacher_remark ?? 'No remark provided.') : 'No remark provided.' ?>
                </div>
            </div>
            <div class="remark-box">
                <div class="remark-title">Principal's Remark</div>
                <div class="remark-content">
                    <?= $resultRecord ? htmlspecialchars($resultRecord->head_teacher_remark ?? 'No remark provided.') : 'No remark provided.' ?>
                </div>
                <?php if($resultRecord && !empty($resultRecord->resumption_date)): ?>
                    <div class="resume-date">Next Term Begins: <?= date('l jS F Y', strtotime($resultRecord->resumption_date)) ?></div>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <?php endforeach; ?>
</body>
</html>
