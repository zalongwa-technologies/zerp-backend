<?php

function syncStudentToZERP($apiClient, $regNumber) {
    global $db;
    
    $resultData = ['status' => false, 'action' => 'none', 'message' => ''];

    // Fetch from API
    try {
        $response = $apiClient->getStudentInfo($regNumber);
        if (!isset($response['statusCode']) || $response['statusCode'] != 200) {
            $msg = "Failed to fetch student info for $regNumber: " . ($response['message'] ?? 'Unknown error');
            $apiClient->logError($msg);
            $resultData['message'] = $msg;
            return $resultData;
        }
        $student = $response['data'];
    } catch (Exception $e) {
        $apiClient->logError("Exception for student $regNumber: " . $e->getMessage());
        $resultData['message'] = $e->getMessage();
        return $resultData;
    }

    $debtorno = DB_escape_string($student['student_regnumber']);
    $name = DB_escape_string(mb_substr($student['student_fullname'], 0, 40));
    $email = DB_escape_string($student['student_email']);
    $phone = DB_escape_string($student['student_phone']);
    $programme = isset($student['student_programme']) ? DB_escape_string($student['student_programme']) : '';
    $entryyear = isset($student['student_entryyear']) ? DB_escape_string($student['student_entryyear']) : '';
    $studyyear = isset($student['student_studyyear']) ? (int)$student['student_studyyear'] : 0;
    $intake = isset($student['student_intake']) ? DB_escape_string($student['student_intake']) : '';


    // Check if debtor exists
    $sql = "SELECT debtorno FROM debtorsmaster WHERE debtorno = '$debtorno'";
    $result = DB_query($sql, $db);

    if (DB_num_rows($result) == 0) {
        // Insert into debtorsmaster
        $sql = "INSERT INTO debtorsmaster (
                    debtorno,
                    name,
                    address1,
                    currcode,
                    salestype,
                    clientsince,
                    holdreason,
                    paymentterms,
                    discount,
                    discountcode,
                    creditlimit,
                    invaddrbranch,
                    taxref,
                    customerpoline,
                    typeid,
                    language_id,
                    student_programme,
                    student_entryyear,
                    student_studyyear,
                    student_intake
                ) VALUES (
                    '$debtorno',
                    '$name',
                    '',
                    'TZS',
                    'L',
                    NOW(),
                    1,
                    'CA',
                    0,
                    '',
                    1000000,
                    0,
                    '',
                    0,
                    1,
                    'en_GB.utf8',
                    '$programme',
                    '$entryyear',
                    $studyyear,
                    '$intake'
                )";
        $ErrMsg = "The customer $debtorno could not be added because";
        $InsertResult = DB_query($sql, $db, $ErrMsg, '', false, false);
        if (DB_error_no($db) != 0) {
            $apiClient->logError("DB Error inserting student $debtorno: " . DB_error_msg($db));
            $resultData['message'] = "DB Error on insert";
            return $resultData;
        }

        // Insert into custbranch
        $sql = "INSERT INTO custbranch (
                    branchcode,
                    debtorno,
                    brname,
                    braddress1,
                    area,
                    salesman,
                    fwddate,
                    defaultlocation,
                    taxgroupid,
                    defaultshipvia,
                    deliverblind,
                    disabletrans,
                    brpostaddr1,
                    estdeliverydays,
                    phoneno,
                    email
                ) VALUES (
                    '$debtorno',
                    '$debtorno',
                    '$name',
                    '',
                    'HO',
                    '',
                    0,
                    'DEF',
                    1,
                    1,
                    1,
                    0,
                    '',
                    1,
                    '$phone',
                    '$email'
                )";
        $ErrMsg = "The customer branch $debtorno could not be added because";
        $InsertResult = DB_query($sql, $db, $ErrMsg, '', false, false);
        
        $resultData['status'] = true;
        $resultData['action'] = 'inserted';
        $resultData['message'] = "Student $debtorno inserted successfully";
    } else {
        // Update existing
        $sql = "UPDATE debtorsmaster SET 
                    name = '$name',
                    student_programme = '$programme',
                    student_entryyear = '$entryyear',
                    student_studyyear = $studyyear,
                    student_intake = '$intake'
                WHERE debtorno = '$debtorno'";
        $UpdateResult = DB_query($sql, $db, '', '', false, false);

        $sql = "UPDATE custbranch SET 
                    brname = '$name',
                    phoneno = '$phone',
                    email = '$email'
                WHERE branchcode = '$debtorno' AND debtorno = '$debtorno'";
        $UpdateResult = DB_query($sql, $db, '', '', false, false);
        
        $resultData['status'] = true;
        $resultData['action'] = 'updated';
        $resultData['message'] = "Student $debtorno updated successfully";
    }

    return $resultData;
}
?>
