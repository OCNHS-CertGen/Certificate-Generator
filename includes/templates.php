<?php
/**
 * templates.php - Centralized configuration for certificate types, body text, and purposes.
 */

/**
 * Returns an array of available certificate types.
 */
function get_certificate_types($include_all = false)
{
    $default_types = [
        'CERTIFICATE OF ENROLLMENT',
        'GOOD MORAL CHARACTER',
        'GOOD MORAL CHARACTER (COLLEGE/SHS ADMISSION)',
        'GOOD MORAL CHARACTER (SCHOOL TRANSFER)',
        'CERTIFICATE OF GRADUATION',
        'CERTIFICATE OF COMPLETION',
        'CERTIFICATE OF RANKING',
        'TRANSFER CERTIFICATION',
        'CERTIFICATE OF NON-ISSUANCE OF YEARBOOK',
        'CERTIFICATE OF NON-ISSUANCE OF ID',
        'LOST ID CERTIFICATION',
        'RECORD DAMAGE CERTIFICATION',
        'SCHOLARSHIP RECOMMENDATION',
        'SCHOOL ACCREDITATION CERTIFICATE'
    ];

    global $conn;

    // Attempt to require config if connection not available
    if (!isset($conn) && file_exists(__DIR__ . '/../config/database.php')) {
        require_once __DIR__ . '/../config/database.php';
    }

    $custom_types = [];
    $disabled_types = [];

    if (isset($conn) && $conn) {
        $res = $conn->query("SELECT certificate_type, status FROM templates");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                if ($row['status'] === 'disabled') {
                    $disabled_types[] = $row['certificate_type'];
                }
                $custom_types[] = $row['certificate_type'];
            }
        }
    }

    $all_types = array_unique(array_merge($default_types, $custom_types));
    $active_types = [];

    foreach ($all_types as $type) {
        if ($include_all || !in_array($type, $disabled_types)) {
            $active_types[] = $type;
        }
    }

    return array_values($active_types);
}

function is_default_template($type)
{
    $default_types = [
        'CERTIFICATE OF ENROLLMENT',
        'GOOD MORAL CHARACTER',
        'GOOD MORAL CHARACTER (COLLEGE/SHS ADMISSION)',
        'GOOD MORAL CHARACTER (SCHOOL TRANSFER)',
        'CERTIFICATE OF GRADUATION',
        'CERTIFICATE OF COMPLETION',
        'CERTIFICATE OF RANKING',
        'TRANSFER CERTIFICATION',
        'CERTIFICATE OF NON-ISSUANCE OF YEARBOOK',
        'CERTIFICATE OF NON-ISSUANCE OF ID',
        'LOST ID CERTIFICATION',
        'RECORD DAMAGE CERTIFICATION',
        'SCHOLARSHIP RECOMMENDATION',
        'SCHOOL ACCREDITATION CERTIFICATE'
    ];
    return in_array($type, $default_types);
}

/**
 * Returns the purpose suggestions for each certificate type.
 */
function get_purpose_map()
{
    return [
        'CERTIFICATE OF ENROLLMENT' => [
            'Pantawid Pamilyang Pilipino Program (4Ps)',
            'Scholarship',
            'Travel',
            'Legal Purposes'
        ],
        'GOOD MORAL CHARACTER (COLLEGE/SHS ADMISSION)' => [
            'College Admission',
            'Senior High School Enrollment'
        ],
        'GOOD MORAL CHARACTER (SCHOOL TRANSFER)' => [
            'School Transfer'
        ],
        'CERTIFICATE OF GRADUATION' => [
            'TESDA',
            'Employment',
            'College Admission'
        ],
        'CERTIFICATE OF COMPLETION' => [
            'Employment',
            'Senior High School Enrollment'
        ],
        'CERTIFICATE OF RANKING' => [
            'Scholarship Application',
            'College Admission',
            'Recognition'
        ],
        'TRANSFER CERTIFICATION' => [
            'School Transfer'
        ],
        'CERTIFICATE OF NON-ISSUANCE OF YEARBOOK' => [
            'Employment Abroad',
            'Immigration',
            'Legal Purposes'
        ],
        'CERTIFICATE OF NON-ISSUANCE OF ID' => [
            'Verification',
            'Legal Purposes',
            'Immigration'
        ],
        'LOST ID CERTIFICATION' => [
            'Replacement',
            'Verification',
            'Legal Purposes'
        ],
        'RECORD DAMAGE CERTIFICATION' => [
            'Record Verification',
            'Replacement',
            'Legal Purposes'
        ],
        'SCHOLARSHIP RECOMMENDATION' => [
            'Scholarship Application',
            'Financial Aid',
            'Educational Grant'
        ],
        'SCHOOL ACCREDITATION CERTIFICATE' => [
            'Verification',
            'Recognition',
            'Legal Purposes'
        ],
        'FORM 137 / SF10' => [
            'School Transfer',
            'Employment',
            'Scholarship Application',
            'Personal Copy'
        ],
        'CERTIFICATION (ENGLISH AS MEDIUM OF INSTRUCTION)' => [
            'Employment Abroad',
            'Visa Requirement',
            'International College Admission',
            'Immigration'
        ]
    ];
}

/**
 * Generates the title and body text based on certificate type.
 */
function get_certificate_content($type, $data)
{
    // Extract data for easier usage in templates
    $sn = "<strong>" . ($data['student_name'] ?? '') . "</strong>";
    $lrn = $data['lrn'] ?? '';
    $lrn_text = !empty($lrn) ? " (<strong>LRN $lrn</strong>)" : "";
    $student_info = "$sn$lrn_text";
    $grade = $data['grade_level'] ?? '';
    $section = $data['section_track'] ?? '';
    $curriculum = $data['curriculum'] ?? '';
    $sy = $data['school_year'] ?? '';
    $purpose = $data['purpose'] ?? '';
    $school_name = $data['school_name'] ?? '';
    $school_address = $data['school_address'] ?? '';
    $division = $data['division'] ?? '';
    $school_level = $data['school_level'] ?? '';

    $content = [
        'title' => 'CERTIFICATE',
        'body' => ''
    ];

    $level_wording = ($school_level === 'SENIOR HIGH SCHOOL') ? 'SENIOR HIGH SCHOOL' : 'JUNIOR HIGH SCHOOL';

    global $conn;
    // Ensure DB connection is loaded
    if (!isset($conn)) {
        if (file_exists(__DIR__ . '/../config/database.php')) {
            require_once __DIR__ . '/../config/database.php';
        }
    }

    // Try fetching from database first
    if (isset($conn) && $conn) {
        $stmt = $conn->prepare("SELECT title, body FROM templates WHERE certificate_type = ?");
        if ($stmt) {
            $stmt->bind_param("s", $type);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $replacements = [
                    '[student_info]' => $student_info,
                    '[sn]' => $sn,
                    '[lrn]' => $lrn,
                    '[lrn_text]' => $lrn_text,
                    '[grade]' => $grade,
                    '[section]' => $section,
                    '[curriculum]' => $curriculum,
                    '[sy]' => $sy,
                    '[purpose]' => $purpose,
                    '[school_name]' => $school_name,
                    '[school_address]' => $school_address,
                    '[division]' => $division,
                    '[level_wording]' => $level_wording
                ];

                $title = str_replace(array_keys($replacements), array_values($replacements), $row['title']);
                $body = str_replace(array_keys($replacements), array_values($replacements), $row['body']);

                return [
                    'title' => $title,
                    'body' => $body
                ];
            }
            $stmt->close();
        }
    }

    // Fallback to defaults

    switch ($type) {
        case 'CERTIFICATE OF ENROLLMENT':
            $content['title'] = "CERTIFICATION";
            $content['body'] = "<p>This is to certify that $student_info is currently enrolled in <strong>$grade - $section</strong> under <strong>$curriculum</strong> this School Year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-mentioned student for <strong>$purpose</strong> purpose only.</p>";
            break;

        case 'CERTIFICATE OF GRADUATION':
            $content['title'] = "CERTIFICATION";
            $content['body'] .= "<p>This is to certify that $student_info graduated under <strong>$curriculum</strong> of the school year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named person as a requirement for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'CERTIFICATE OF COMPLETION':
            $content['title'] = "CERTIFICATE OF COMPLETION";
            $content['body'] .= "<p>This is to certify that $sn with <strong>LRN $lrn</strong> was a <strong>$grade COMPLETER</strong> of this school under <strong>$curriculum</strong> during the school year <strong>$sy</strong>. He/She has satisfactory completed the requirements of Junior High School.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named person as a requirement for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'GOOD MORAL CHARACTER':
            $content['title'] = "CERTIFICATE OF<br>GOOD MORAL CHARACTER";
            $content['body'] = "<p>This is to certify that $sn was a bona fide student of this school under <strong>$curriculum</strong> during the school year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>The student has no derogatory records filed in this office as of this date.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named student as a requirement for <strong>$purpose</strong>.</p>";
            break;

        case 'GOOD MORAL CHARACTER (SCHOOL TRANSFER)':
            $content['title'] = "CERTIFICATE OF<br>GOOD MORAL CHARACTER";
            $content['body'] = "<p>This is to certify that $student_info was a bona fide <strong>$grade &ndash; $section</strong> student of this school under <strong>$curriculum</strong> during the school year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>The student has no derogatory records filed in this office as of this date.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named student as a requirement for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'GOOD MORAL CHARACTER (COLLEGE/SHS ADMISSION)':
            $content['title'] = "CERTIFICATE OF<br>GOOD MORAL CHARACTER";
            $level_wording = ($school_level === 'SENIOR HIGH SCHOOL') ? 'SENIOR HIGH SCHOOL' : 'JUNIOR HIGH SCHOOL';
            $content['body'] = "<p>This is to certify that $student_info is a bona fide <strong>$grade &ndash; $section</strong> student of this school under <strong>$curriculum</strong> . He/She has not violated any act or omission punishable by the school rules and regulation, and he/she is in the list of those completing <strong>$level_wording</strong> this school year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named student as a requirement for <strong>$purpose</strong> purpose only.</p>";
            break;

        case 'CERTIFICATE OF RANKING':
            $content['title'] = "CERTIFICATE OF RANKING";
            $content['body'] = "<p>This is to certify that $student_info is officially ranked among the students of <strong>$grade - $section</strong> under <strong>$curriculum</strong> during the School Year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named student for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'CERTIFICATE OF NON-ISSUANCE OF YEARBOOK':
            $content['title'] = "CERTIFICATE OF<br>NON-ISSUANCE OF YEARBOOK";
            $content['body'] = "<p>This is to certify that $sn graduated from this school under <strong>$curriculum</strong> during the School Year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>This certifies further that there was no Annual Yearbook issued during that year.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named person for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'CERTIFICATE OF NON-ISSUANCE OF ID':
            $content['title'] = "CERTIFICATE OF<br>NON-ISSUANCE OF SCHOOL ID";
            $content['body'] = "<p>This is to certify that $sn was a bona fide <strong>$grade - $section</strong> student of this school during the School Year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>This certifies further that <strong>NO SCHOOL IDENTIFICATION CARD</strong> was issued to the above-named student during that school year.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named person for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'LOST ID CERTIFICATION':
            $content['title'] = "CERTIFICATION OF<br>LOST SCHOOL ID";
            $content['body'] = "<p>This is to certify that $student_info is a bona fide <strong>$grade - $section</strong> student of this school under <strong>$curriculum</strong> for the School Year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>This certifies further that the student has reported the loss of their School Identification Card issued by this institution.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named student for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'RECORD DAMAGE CERTIFICATION':
            $content['title'] = "CERTIFICATION OF<br>DAMAGED SCHOOL RECORDS";
            $content['body'] = "<p>This is to certify that certain school records of $student_info, a <strong>$grade - $section</strong> student of this school, have been damaged or rendered partially illegible.</p>";
            $content['body'] .= "<p>This certification is issued to acknowledge the condition of the records and to facilitate any necessary verification or replacement procedures.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named student for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'SCHOLARSHIP RECOMMENDATION':
            $content['title'] = "SCHOLARSHIP<br>RECOMMENDATION";
            $content['body'] = "<p>This is to recommend $student_info, a <strong>$grade - $section</strong> student of this school under <strong>$curriculum</strong> during the School Year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>Based on academic performance and conduct, the student is hereby recommended for scholarship consideration.</p>";
            $content['body'] .= "<p>This recommendation is issued to support the student's application for <strong>$purpose</strong>.</p>";
            break;

        case 'TRANSFER CERTIFICATION':
            $content['title'] = "TRANSFER<br>CERTIFICATION";
            $content['body'] = "<p>This is to certify that $student_info was a bona fide student of this school under <strong>$curriculum</strong> during the School Year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>This certifies further that the student is cleared of all property and financial responsibilities from this institution and is hereby granted honorable dismissal to transfer to another school.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named student for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'SCHOOL ACCREDITATION CERTIFICATE':
            $content['title'] = "SCHOOL<br>ACCREDITATION CERTIFICATE";
            $content['body'] = "<p>This is to certify that <strong>$school_name</strong>, located at <strong>$school_address</strong>, is a duly recognized and accredited public secondary school under the <strong>Department of Education - $division</strong>.</p>";
            $content['body'] .= "<p>The school operates under the <strong>K TO 12 BASIC EDUCATION PROGRAM</strong> and is authorized to issue official certifications and academic credentials.</p>";
            $content['body'] .= "<p>This certification is issued for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'RECONSTRUCTED DIPLOMA':
            $content['title'] = "RECONSTRUCTED DIPLOMA";
            $content['body'] = "
            <div class='diploma-intro'>Pinatutunayan nito na si</div>
            <div class='diploma-intro-en'>This certifies that</div>
            
            <div class='student-name-wrapper'>
                <div class='student-name-cert'>$sn</div>
            </div>
            
            <div class='diploma-paragraph'>
                ay kasiya-siya nakatupad sa mga kinakailangan sa pagtatapos ng
                <br>
                <em class='diploma-paragraph-en'>has satisfactorily completed the requirements for graduation in accordance with the</em>
                <strong>BAGONG KURIKULUM SA EDUKASYONG SEKUNDARYA</strong> na itinakda para sa mataas na paaralan ng
                <br>
                <em class='diploma-paragraph-en'>New Secondary Education Curriculum prescribed for secondary schools of the</em>
                Republika ng Pilipinas, kaya siya'y karapat-dapat tumanggap nitong
                <br>
                <em class='diploma-paragraph-en'>Republic of the Philippines and is therefore entitled to this</em>
            </div>
            
            <div class='katibayan-section'>
                <div class='diploma-label-reconstructed'>RECONSTRUCTED</div>
                <div class='diploma-label-text'>DIPLOMA</div>
            </div>";
            break;

        case 'FORM 137 / SF10':
            $content['title'] = "CERTIFICATION";
            $content['body'] = "<p>This is to certify that $student_info was a bona fide student of this school under <strong>$curriculum</strong> during the school year <strong>$sy</strong>.</p>";
            $content['body'] .= "<p>This further certifies that his/her <strong>Official Transcript of Records (Form 137 / SF10-ES)</strong> is available and remains in the custody of this institution.</p>";
            $content['body'] .= "<p>This certification is issued upon the request of the above-named student for <strong>$purpose</strong> purposes.</p>";
            break;

        case 'CERTIFICATION (ENGLISH AS MEDIUM OF INSTRUCTION)':
            $content['title'] = "CERTIFICATION";
            $content['body'] = "<p>This is to certify that $sn graduated in this school during school year <strong>$sy</strong> under <strong>$curriculum</strong></p>";
            $content['body'] .= "<p>This further certifies that the <strong>Medium of Instruction</strong> used for teaching in this school is in <strong>English</strong> language except for the subject <strong>Filipino</strong>.</p>";
            $content['body'] .= "<p>This certification is being issued upon the request of the above-named person for <strong>$purpose</strong> purposes.</p>";
            break;

        default:
            $content['body'] = "<p>This is to certify that $sn.</p>";
            break;
    }

    return $content;
}

/**
 * Returns a structured array of curriculum options grouped by category.
 * Used for populating search and dropdown menus.
 */
function get_curriculum_list()
{
    return [
        'Historical (2-2 Plan)' => [
            '1969 - 1970 2-2 PLAN (COLEGE PREPARATORY)',
            '1970 - 1971 2-2 PLAN (COLEGE PREPARATORY)',
            '1971 - 1972 2-2 PLAN (COLEGE PREPARATORY)',
            '1972 - 1973 2-2 PLAN (COLEGE PREPARATORY)',
            '1973 - 1974 2-2 PLAN (COLEGE PREPARATORY)',
            '1974 - 1975 2-2 PLAN (COLEGE PREPARATORY)',
            '1975 - 1976 2-2 PLAN (COLEGE PREPARATORY)',
        ],
        'Revised Secondary Education (RSEP)' => [
            '1976 - 1977 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1977 - 1978 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1978 - 1979 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1979 - 1980 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1980 - 1981 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1981 - 1982 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1982 - 1983 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1983 - 1984 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1984 - 1985 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1985 - 1986 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1986 - 1987 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1987 - 1988 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1988 - 1989 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1989 - 1990 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1990 - 1991 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
            '1991 - 1992 REVISED SECONDARY EDUCATION PROGRAM (RSEP)',
        ],
        'New Secondary Education (NSEC)' => [
            '1992 - 1993 NEW SECONDARY EDUCATION CURRICULUM (NSEC)',
            '1993 - 1994 NEW SECONDARY EDUCATION CURRICULUM (NSEC)',
            '1994 - 1995 NEW SECONDARY EDUCATION CURRICULUM (NSEC)',
            '1995 - 1996 NEW SECONDARY EDUCATION CURRICULUM (NSEC)',
            '1996 - 1997 NEW SECONDARY EDUCATION CURRICULUM (NSEC)',
            '1997 - 1998 NEW SECONDARY EDUCATION CURRICULUM (NSEC)',
            '1998 - 1999 NEW SECONDARY EDUCATION CURRICULUM (NSEC)/SPECIAL SCIENCE CURRICULUM-ENGINEERING & SCIENCE EDUCATION PROJECT (SSC-ESEP)',
            '1999 - 2000 NEW SECONDARY EDUCATION CURRICULUM (NSEC)',
            '2000 - 2001 NEW SECONDARY EDUCATION CURRICULUM (NSEC)',
            '2001 - 2002 NEW SECONDARY EDUCATION CURRICULUM (NSEC)',
            '2002 - 2003 NEW SECONDARY EDUCATION CURRICULUM (NSEC)/SPECIAL SCIENCE CLASS-ENGINEERING &SCIENCE EDUCATION PROJECT (SSC-ESEP)',
        ],
        'Basic Education Curriculum (BEC)' => [
            '2003 - 2004 BASIC EDUCATION CURRICULUM (BEC)/MAKABAYAN',
            '2004 - 2005 BASIC EDUCATION CURRICULUM (BEC)',
            '2005 - 2006 BASIC EDUCATION CURRICULUM (BEC)',
            '2006 – 2007 BASIC EDUCATION CURRICULUM (BEC)/DLP',
            '2007 - 2008 BASIC EDUCATION CURRICULUM (BEC)/SPECIAL SCIENCE CURRICULUM',
            '2008 - 2009 BASIC EDUCATION CURRICULUM (BEC)',
            '2009 - 2010 BASIC EDUCATION CURRICULUM (BEC)',
            '2010 - 2011 BASIC EDUCATION CURRICULUM (BEC)',
            '2011 - 2012 BASIC EDUCATION CURRICULUM (BEC)',
            '2012 - 2013 BASIC EDUCATION CURRICULUM (BEC)',
        ],
        'SEC/EBEC Programs' => [
            '2013 - 2014 SECONDARY EDUCATION CURRICULUM (SEC)',
            '2014 - 2015 ENHANCED BASIC EDUCATION CURRICULUM (EBEC)',
            '2015 - 2016 K-12 BASIC EDUCATION PROGRAM (BEP)/SPECIAL SCIENCE CURRICULUM-SCIENCE&TECHNOLOGY ENGINEERING PROGRAM',
            '2016 - 2017 BASIC EDUCATION PROGRAM (BEP)/ARABIC LANGUAGE & ISLAMIC VALUES EDUCATION (ALIVE)',
            '2017 – 2018 BASIC EDUCATION PROGRAM (BEP)/ MADARASAH EDUCATION PROGRAM/SCIENCE TECHNOLOGY&ENGINEERING PROGRAM (STEP)/SPECIAL PROGRAM IN TECHNICAL-VOCATIONAL EDUCATION (SPTVE)',
        ],
        'Current K-12 Programs' => [
            'K TO 12 SCIENCE TECHNOLOGY AND ENGINEERING (STE)',
            'K TO 12 SPECIAL PROGRAM IN THE ARTS (SPA)',
            'K TO 12 SPECIAL PROGRAM IN FOREIGN LANGUAGES (SPFL)',
            'K TO 12 SPECIAL PROGRAM IN SPORTS (SPS)',
            'K TO 12 SPECIAL PROGRAM IN TECHNICAL -VOCATIONAL EDUCATION (SPTVE)',
            'K TO 12 SPECIAL PROGRAM IN JOURNALISM (SPJ)',
            'K TO 12 BASIC EDUCATION PROGRAM (BEP)',
            'K TO 12 OPEN HIGH SCHOOL PROGRAM (OHSP)',
            'K TO 12 SCIENCE, TECHNOLOGY, ENGINEERING AND MATHEMATICS (STEM)',
            'K TO 12 ARTS AND DESIGN (ADT)',
            'K TO 12 TECHNICAL AND VOCATIONAL LIVELIHOOD (TVL)',
        ],
    ];
}
