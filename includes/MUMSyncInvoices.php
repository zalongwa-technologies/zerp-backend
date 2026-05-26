<?php

function syncInvoiceToZERP($apiClient, $invoice) {
    global $db;
    
    $resultData = ['status' => false, 'action' => 'none', 'message' => ''];

    $ref = DB_escape_string($invoice['invoice_reference_number']);
    $debtorno = DB_escape_string($invoice['student_regnumber']);
    $amount = (float)$invoice['invoice_amount'];
    $amountType = isset($invoice['invoice_amount_type']) ? DB_escape_string($invoice['invoice_amount_type']) : '';
    $desc = DB_escape_string($invoice['invoice_desciption']);
    $date = date('Y-m-d', strtotime($invoice['invoice_date']));

    // Check if invoice already exists
    $sql = "SELECT transno FROM debtortrans WHERE reference = '$ref' AND type = 10 AND debtorno = '$debtorno'";
    $result = DB_query($sql, $db);

    if (DB_num_rows($result) > 0) {
        $resultData['status'] = true;
        $resultData['action'] = 'skipped';
        $resultData['message'] = "Invoice $ref already exists";
        return $resultData; 
    }

    DB_Txn_Begin($db);

    // Get next transaction number for type 10 (Sales Invoice)
    $transno = GetNextTransNo(10, $db);
    $periodNo = GetPeriod($date, $db);

    // Insert into debtortrans
    $sql = "INSERT INTO debtortrans (
            transno,
            type,
            debtorno,
            branchcode,
            trandate,
            prd,
            reference,
            tpe,
            ovamount,
            ovgst,
            ovfreight,
            ovdiscount,
            diffonexch,
            alloc,
            invtext,
            shipvia,
            edisent,
            invoice_amount_type
        ) VALUES (
            $transno,
            10,
            '$debtorno',
            '$debtorno',
            '$date',
            $periodNo,
            '$ref',
            'L',
            $amount,
            0,
            0,
            0,
            0,
            0,
            '$desc',
            1,
            0,
            '$amountType'
        )";

    $ErrMsg = "Cannot insert debtortrans for $ref";
    $Result = DB_query($sql, $db, $ErrMsg, '', false, false);
    if (DB_error_no($db) != 0) {
        DB_Txn_Rollback($db);
        $apiClient->logError("DB Error inserting invoice $ref: " . DB_error_msg($db));
        $resultData['message'] = "DB Error inserting invoice";
        return $resultData;
    }

    // Default GL accounts (you may need to change these based on actual ZERP setup)
    $DebtorsGLCode = 1100; // Debtors Control
    $SalesGLCode = 4100;   // Sales Account

    // Insert into gltrans (Debtors Control - Debit)
    $sql = "INSERT INTO gltrans (
            type,
            typeno,
            trandate,
            periodno,
            account,
            narrative,
            amount
        ) VALUES (
            10,
            $transno,
            '$date',
            $periodNo,
            $DebtorsGLCode,
            '$debtorno - $desc',
            $amount
        )";
    $Result = DB_query($sql, $db, $ErrMsg, '', false, false);

    // Insert into gltrans (Sales - Credit)
    $sql = "INSERT INTO gltrans (
            type,
            typeno,
            trandate,
            periodno,
            account,
            narrative,
            amount
        ) VALUES (
            10,
            $transno,
            '$date',
            $periodNo,
            $SalesGLCode,
            '$debtorno - $desc',
            -$amount
        )";
    $Result = DB_query($sql, $db, $ErrMsg, '', false, false);

    DB_Txn_Commit($db);

    $resultData['status'] = true;
    $resultData['action'] = 'inserted';
    $resultData['message'] = "Invoice $ref inserted successfully as transno $transno";
    return $resultData;
}
?>
