<?php
/*
 * num: show how many emails
 * to : show To address
 * subject : show Subject
 * list : show To address and Subject
 */


include_once ('popinc.php');
if (!isset($mailbox) || (mb_strlen($mailbox) == 0)) {
  echo "[ERROR] variable mailbox not set.\n";
  return;
}
if (!isset($user) || (mb_strlen($user) == 0)) {
  echo "[ERROR] varaiable user not set.\n";
  return;
}
if (!isset($pass) || (mb_strlen($pass) == 0)) {
  echo "[ERROR] variable pass not set.\n";
  return;
}

//$mode = false;
$mode_show_only_num = false;
$mode_show_to_address = false;
$mode_show_subject = false;
$mode_do_not_skip = false;
$mode_read_mail = false;
$mode_del_mail = false;
$mail_num = 0;
if (count($argv) > 1) {
  for ($i = 1; $i < count($argv); $i++) {
    $a = mb_convert_case($argv[$i], MB_CASE_LOWER);
    if ($a == 'num') {
      $mode_show_only_num = true;
      break;
    }
    if ($a == 'to') {
      $mode_show_to_address = true;
    }
    if ($a == 'subject') {
      $mode_show_subject = true;
    }
    if ($a == 'list') {
      $mode_show_to_address = true;
      $mode_show_subject = true;
      $mode_do_not_skip = true;
      $i += 1;
      if (isset($argv[$i])) {
        $a = mb_convert_case($argv[$i], MB_CASE_LOWER);
        if ($a == 'del') {
          $mode_del_mail = true;
          $mail_num = -1;
        }

      }
    }
    if ($a == 'read') {
      $i += 1;
      if (!isset($argv[$i])) {
        echo "[ERROR] mail number required.\n";
        return;
      }
      $x = intval($argv[$i]);
      if ($x == 0) {
        echo "[ERROR] invalid mail number specified.\n";
        return;
      }
      $mode_read_mail = true;
      $mail_num = $x;
    }
    if (($a == 'del') && ($mail_num == 0)) {
      $i += 1;
      if (!isset($argv[$i])) {
        echo "[ERROR] mail number required.\n";
        return;
      }
      $x = intval($argv[$i]);
      if ($x == 0) {
        echo "[ERROR] invalid mail number specified.\n";
        return;
      }
      $mode_del_mail = true;
      $mail_num = $x;
    }
  }
}

$mbox = imap_open($mailbox, $user, $pass);
if ($mbox === FALSE) {
  echo "[ERROR] failed to connect.\n";
  return;
}

$num = imap_num_msg($mbox);
if (is_numeric($num)) {
  echo $num." emails.\n\n";
} else {
  var_dump($num);
  return;
}

if (($mode_read_mail || $mode_del_mail) && ($mail_num > 0)) {
  // read and/or delete email
  if ($mail_num > $num) {
    echo "[ERROR] Invalid mail number speciied.\n";
    imap_close($mbox);
    return;
  }

  if (0) {
    $h = imap_fetchheader($mbox, $mail_num);
    if ($h === FALSE) {
      printf("[%4d][ERROR] Failed to get header.\n", $mail_num);
      imap_close($mbox);
      return;
    }
    if (0) var_dump($h);

    printf("Mail number: %d\n", $mail_num);

    $s = iconv_mime_decode_headers($h);

    echo "From: ";
    if (isset($s['From'])) {
      echo $s['From'];
    } else {
      echo "(empty)";
    }
    echo "\n";

    echo "To: ";
    if (isset($s['To'])) {
      echo $s['To'];
    } else {
      echo "(empty)";
    }
    echo "\n";

    echo "Subject: ";
    if (isset($s['Subject'])) {
      echo $s['Subject'];
    } else {
      echo "(empty)";
    }
    echo "\n";
  } else {
    $h = imap_headerinfo($mbox, $mail_num);
    if ($h === FALSE) {
      printf("[%4d][ERROR] Failed to get header.\n", $mail_num);
      imap_close($mbox);
      return;
    }
    if (0) var_dump($h);

    printf("Mail number: %d\n", $mail_num);

    echo "From: ";
    $tmp = '';
    if (isset($h->fromaddress)) {
      $tmp = iconv_mime_decode($h->fromaddress);
      if (mb_strlen($tmp) == 0) {
        $tmp = $h->fromaddress;
      }
    } else {
      $tmp = '(empty)';
    }
    echo $tmp."\n";

    echo "To: ";
    $tmp = '';
    if (isset($h->toaddress)) {
      $tmp = iconv_mime_decode($h->toaddress);
      if (mb_strlen($tmp) == 0) {
        $tmp = $h->toaddress;
      }
    } else {
      $tmp = '(empty)';
    }
    echo $tmp."\n";

    echo "Subject: ";
    if (isset($h->subject)) {
      $tmp = iconv_mime_decode($h->subject);
      if (mb_strlen($tmp) == 0) {
        $tmp = $h->subject;
      }
    } else {
      $tmp = '(empty)';
    }
    echo $tmp."\n";
  }

  echo "--------\n";

  if ($mode_del_mail) {
    echo "--------\n";
    printf("   delete? (y/N): ");
    $stdin = trim(fgets(STDIN));
    if (($stdin == 'Y') || ($stdin == 'y')) {
      // mark as delete
      imap_delete($mbox, $mail_num);
    }
    imap_expunge($mbox);
  }

} else {
  // show email list
  if ($num > 0) {
    for ($i = 1; $i <= $num; $i++) {
      if (0) {
        $h = imap_fetchheader($mbox, $i);
        if ($h === FALSE) {
          printf("[%4d][ERROR] Failed to get header.\n", $i);
          continue;
        }

        printf("[%4d] ", $i);

        $s = iconv_mime_decode_headers($h);

        if (isset($s['To'])) {
          echo $s['To'];
        }
        echo "\n";

        if (isset($s['Subject'])) {
          echo "    ".$s['Subject'];
        }
        echo "\n";
      } else {
        $h = imap_headerinfo($mbox, $i);
        if ($h === FALSE) {
          printf("[%4d][ERROR] Failed to get header.\n", $i);
          continue;
        }

        printf("[%4d]\n", $i);

        echo "  From: ";
        $tmp = '';
        if (isset($h->fromaddress)) {
          $tmp = iconv_mime_decode($h->fromaddress);
          if (mb_strlen($tmp) == 0) {
            $tmp = $h->fromaddress;
          }
        } else {
          $tmp = '(empty)';
        }
        echo $tmp."\n";
    
        echo "  To: ";
        $tmp = '';
        if (isset($h->toaddress)) {
          $tmp = iconv_mime_decode($h->toaddress);
          if (mb_strlen($tmp) == 0) {
            $tmp = $h->toaddress;
          }
        } else {
          $tmp = '(empty)';
        }
        echo $tmp."\n";
    
        echo "  Subject: ";
        if (isset($h->subject)) {
          $tmp = iconv_mime_decode($h->subject);
          if (mb_strlen($tmp) == 0) {
            $tmp = $h->subject;
          }
        } else {
          $tmp = '(empty)';
        }
        echo $tmp."\n";
      }

      if ($mode_del_mail) {
        echo "--------\n";
        printf("   delete? (y/N): ");
        $stdin = trim(fgets(STDIN));
        if (($stdin == 'Y') || ($stdin == 'y')) {
          // mark as delete
          imap_delete($mbox, $i);
        }
      }
    
    }

    if ($mode_del_mail) {
      imap_expunge($mbox);
    }
    
  }
}


imap_close($mbox);

// //$accept_addrs = array('vector.co.jp');

// $mbox = imap_open($mailbox, $user, $pass);

// if ($mbox === FALSE) {
//   echo "[ERROR] failed to connect.\n";
//   return;
// }

// $num = 0;

// $n = imap_num_msg($mbox);
// if ($mode_show_only_num) {
//   if (is_numeric($n)) {
//     printf("%d emails.\n", $n);
//   } else {
//     var_dump($n);
//   }
// } else if (($mode_read_mail > 0) || ($mode_del_mail > 0)) {
//   /*
//    * read mail or delete mail
//    */
//   if ($mode_read_mail > 0) {
//     $i = $mode_read_mail;
//   } else if ($mode_del_mail > 0) {
//     $i = $mode_del_mail;
//   }
//   if ($i == 0) {
//     echo "[ERROR] something wrong.\n";
//     imap_close($mbox);
//     return;
//   }
//   $h = imap_fetchheader($mbox, $i);
//   $subject = imap_mime_header_decode($h->subject);
//   $subject = $subject[0]->text;
//   echo "[".$subject."]\n";
  
//   echo $h;
//   echo "\n--------\n";
//   $b = imap_body($mbox, $i);
//   // TODO: limit body length
//   $b = mb_split("\n", $b);
//   $l = count($b);
//   if ($l > 30) $l = 30;
//   for ($m = 0; $m < $l; $m++) {
//     echo $b[$m]."\n";
//   }
//   //echo $b;
//   echo "\n";

//   if ($mode_del_mail > 0) {
//     echo "\n----------------\n";
//     printf("   delete? (y/N): ");
//     $stdin = trim(fgets(STDIN));
//     if (($stdin == 'Y') || ($stdin == 'y')) {
//       // mark as delete
//       imap_delete($mbox, $i);
//       imap_expunge($mbox);
//     }
//   }
// } else {
//   /*
//    * show list
//    */
//   if (is_numeric($n) && ($n > 0)) {
//     for ($i = 1; $i <= $n; $i++) {
//       $mail = '';

//       $h = imap_headerinfo($mbox, $i);
//       if ($h === FALSE) {
//          printf("[ERROR][%3d] Failed to get header.\n", $i);
//          continue;
//       }

//       if ($mode_do_not_skip) {
//         $f = false;
//         foreach ($accept_addrs as $a) {
//           $p = mb_stripos($h->toaddress, $a);
//           if ($p !== FALSE) {
//             $f = true;
//           }
//           if ($f) {
//             break;
//           }
//         }
//         if ($f) {
//           continue;
//         }
//       }
//       //printf("[%3d] %s\n", $i, $h->toaddress);

//       $mail = sprintf("[%3d] ", $i);

//       if ($mode_show_to_address) {
//         $mail .= $h->toaddress."\n";
//       }

//       if ($mode_show_subject) {

//         /*
//         //$s = iconv_mime_decode($h->subject);
//         $s = imap_mime_header_decode($h->subject);
//         if (isset($s[0]->text)) {
//         if (mb_strlen(trim($s[0]->text)) == 0) {
//           $s = $h->subject;
//         }
//       } else {
//         $s = $h->subject;
//       }
//       */
//         $s = imap_mime_header_decode($h->subject);
//         $s = $s[0]->text;
//         $mail .= "    [".$s."]\n";
//       }
//       echo $mail;
//       /*
//       if ($mode == 'del') {
//         printf("   delete? (y/N): ");
//         $stdin = trim(fgets(STDIN));
//         if (($stdin == 'Y') || ($stdin == 'y')) {
//           // mark as delete
//           imap_delete($mbox, $i);
//         }
//       }
//       */
//       /*
//       $num++;
//       if ($num > 10) break;
//       */
//     }
//   }

//   // delete
//   if ($mode == 'del') {
//     imap_expunge($mbox);
//   }
// }

// imap_close($mbox);

