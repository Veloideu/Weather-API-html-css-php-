<?php

/*
=========================================
# BY. DESIGNBABY [라이센스 인증 구현]

공개용 마지막 업데이트 2019/05/31 [ 안티연구소 ]
[ 공개용 소스와 비공개용 소스는 다릅니다. ]

부제:  서버와 안드로이드 앱 또는 윈도우 응용프로그램과 통신
데이터 테이블 생성이 매우 쉬워졌습니다. (*테이블 자동설치)
* 강력해진 보안! 라이센스 코드로 쉽게 가입과 관리! *

# 공개용 소스
서버:
테이블 자동셋팅 PHP
서버인증 및 DB관리 PHP
윈도우 또는 안드로이드 관리자 프로그램
클라이언트:
C++ WINAPI, VB6.0, AutoHotKey

[-] 구현 기능
- 임의로 주소를 적고 들어올 경우 리덱션이 되어
  특정 페이지로 넘어가거나 차단되어 오류가 발생 됩니다.
- SQL 인젝션 방어로 인해 POST 변수의 안정성이 확보되었습니다.
- 난수의 랜덤 코드를 생성 합니다.
- 코드가 존재 하지않는다면 회원 가입을 합니다. 
  [ 클라이언트에서 전송 된 코드가 코드 테이블에 있을 경우만 가입이 진행 됨. ]
- 클라이언트 유효기간 날짜 및 일수를 계산하여 출력 합니다.
- 윈도우 및 안드로이드 관리자용 플랫폼에서
  유효기간이 지난 계정들을 자동으로 삭제 시켜줍니다.

[ 비공개용 솔루션의 기능 ]
- 클라이언트에 전송할 값을 입력 할 수 있습니다.
  따라서 클라이언트를 크랙할 경우라도
  서버에 인증이되지 않으면 사용을 할 수 없습니다.
- Private Plugin [ 개인 임대솔루션 ]
  메인서버에 Private로 등록 된 라이센스를 입력하면
  타 서버 혹은 사이트에서 메인서버의 기능을 해당 기간동안 사용 할 수 있습니다.
[ Private 별도구입 ]
- 랜덤 VPN 서비스 [ 서버의 IP가 랜덤으로 바뀌게 됩니다. ]
- DDos 공격 패킷을 감지하여 접속 IP를 차단 합니다.

다음 기능 업데이트는 비공개용 소스만 진행 됩니다.
오픈 소스는 이번 업데이트가 끝 입니다.
=========================================
*/

// phpMyAdmin MySQL
$DB_Host = 'localhost'; // <- 수정금지
/* 아래의 3개의 변수에 DB 양식을 입력해주세요. */
$DB_User =  'root';
$DB_Pw   = 'vkfeh123';
$DB_Database =  'member';

// SQL 연결
$conn=mysqli_connect($DB_Host, $DB_User, $DB_Pw, $DB_Database);
if (!$conn) // 서버연결 실패
{
	mysqli_close($conn);
}

// SQL 인젝션 방어
function anti_SQL_Injection($sql, $text = true)
{
	$sql = preg_replace("/(from|select|insert|delete|where|drop table|show tables|,|'|#|\*|--|\\\\)/i","",$sql);
	$sql = trim($sql);
	$sql = strip_tags($sql);
	if(!$text || !get_magic_quotes_gpc())
	  $sql = addslashes($sql);
	return $sql;
}

// 랜덤 난수생성
function GetRandNumber($total_num, $getCount = 1){
	srand((double)microtime()*1000000);
	$ret_array = Array();
	while(true){
	   $ret_array[] = rand(1,$total_num);
	   $ret_array = array_unique($ret_array);
	if (count($ret_array) == $getCount) break;
	}
	return $ret_array;
}

// 랜덤 코드생성
function GetCodeNum($string_length){
	$ArrayAlpha = Array(
	'A','B','C','D','E','F','G','H','I','J','K','L',
	'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
	'1','2','3','4','5','6','7','8','9','0');
	$resultString = '';
	for ( $i=0; $i<$string_length; $i++) {
        	$resultRand = GetRandNumber(count($ArrayAlpha));
        	$resultString .= $ArrayAlpha[ $resultRand[0]-1 ];
	}
	return $resultString;
}

/* 코드생성 */
if (isset($_POST['Code']) or isset($_POST['CodeDate']))
{
	$vCode = anti_SQL_Injection($_POST['Code']); /*GetCodeNum(뽑을 코드개수)*/
	$vDate = anti_SQL_Injection($_POST['CodeDate']);
	$sql_add = "INSERT INTO Code(idx, code, date) VALUES(NULL, '$vCode', '$vDate')";
	echo $vCode."</br>";
	mysqli_query($conn, $sql_add);
	mysqli_close($conn);
}

/* 코드삭제 */
if (isset($_POST['C_Delete']))
{
	$MyCode = anti_SQL_Injection($_POST['C_Delete']);
	$sql="DELETE FROM Code WHERE code='$MyCode'";
	mysqli_query($conn, $sql);
	mysqli_close($conn);
}

/* 맴버코드 삭제 */
if (isset($_POST['M_Delete']))
{
	$OutMem = anti_SQL_Injection($_POST['M_Delete']);
	$sql="DELETE FROM Member WHERE code='$OutMem'";
	mysqli_query($conn, $sql);
	mysqli_close($conn);
}

/* 맴버 접속상태 수정 */
if (isset($_POST['M_Edit']) or isset($_POST['M_Status']))
{
	$Code = anti_SQL_Injection($_POST['M_Edit']);
	$Num = anti_SQL_Injection($_POST['M_Status']);
	
	// 코드 가져오기
	$check="SELECT * FROM Member WHERE code='$Code'";
	$result=mysqli_query($conn, $check);
	$num_rows = mysqli_num_rows($result);
	
	if($num_rows == 1) // 코드가 존재한다.
	{
		$sql_add = "UPDATE Member SET account='$Num' WHERE code='$Code'";
		mysqli_query($conn, $sql_add);
	}
	mysqli_free_result($result);
	mysqli_close($conn);
}

/* 맴버 사용기한 수정 */
if (isset($_POST['M_Code']) or isset($_POST['M_Date']))
{
	$v_Code = anti_SQL_Injection($_POST['M_Code']);
	$v_Date = anti_SQL_Injection($_POST['M_Date']);
	
	// 코드 가져오기
	$check="SELECT * FROM Member WHERE code='$v_Code'";
	$result=mysqli_query($conn, $check);
	$num_rows = mysqli_num_rows($result);

	if($num_rows == 1) // 코드가 존재한다.
	{
		$sql_add = "UPDATE Member SET date='$v_Date' WHERE code='$v_Code'";
		mysqli_query($conn, $sql_add);
	}

	mysqli_free_result($result);
	mysqli_close($conn);
}

/* 코드 목록보기 */
if (isset($_POST['CodeList']))
{
	$check = "SELECT * FROM Code";
	$result = mysqli_query($conn, $check);

	while($row = mysqli_fetch_array($result))
	{
		echo $row['code']."|".$row['date']."</br>";
	}
	mysqli_free_result($result);
	mysqli_close($conn);
}

/* 맴버 목록보기 */
if (isset($_POST['MemberList'])) 
{
	$u_Today = date("Y-m-d", time());
	$check = "SELECT * FROM Member";
	$result = mysqli_query($conn, $check);
	
	while($row = mysqli_fetch_array($result))
	{
		$u_Code = $row['code'];
		$u_Account = $row['account'];
		$u_Date = $row['date'];
		$u_Ip = $row['address'];
		$str = $u_Code.'|'.$u_Account.'|'.$u_Date.'|'.$u_Ip;
		$u_DDays = ( strtotime($u_Date) - strtotime($u_Today) ) / 86400;
		
		echo $str."</br>";
		
		// 관리자 리스트 갱신 할 때마다 자동삭제
		if($u_DDays <= -1)
		{
			// 코드 삭제
			$CodeDel="DELETE FROM Member WHERE code='$u_Code'";
			mysqli_query($conn, $CodeDel);
		}
	}
	
	mysqli_free_result($result);
	mysqli_close($conn);
}

/* 로그인 */
if (isset($_POST['In']))
{
	$InCode = anti_SQL_Injection($_POST['In']);
	
	// 코드 가져오기
	$check="SELECT * FROM Member WHERE code='$InCode'";
	$result=mysqli_query($conn, $check);
	$num_rows = mysqli_num_rows($result);
	$row=mysqli_fetch_array($result);
	$u_Code= $row['code'];
	$u_Account= $row['account'];
	$u_Date= $row['date'];
	$u_Today = date("Y-m-d", time());
	$u_DDays = ( strtotime($u_Date) - strtotime($u_Today) ) / 86400;
	
	if($num_rows == 1) // 코드가 존재한다.
	{
		$sql_add = "UPDATE Member SET account='1' WHERE code='$u_Code'";
		mysqli_query($conn, $sql_add);
		$str = '|'.$u_Account.'|'.$u_Date.' ('.$u_DDays.' days)'; // 인증결과 값 - |1|유효기간 (남은일수)
		echo $str;
	}
	else // 코드가 없다.
	{
		$m_table="SELECT * FROM Code WHERE code='$InCode'";
		$m_query=mysqli_query($conn, $m_table);
		$z_num_rows = mysqli_num_rows($m_query);
		
		if($z_num_rows == 1)
		{
			$z_row=mysqli_fetch_array($m_query);
			
			// 유효기한 자동설정
			$Days = $z_row['date'];
			$diff = date("Y-m-d", mktime(0,0,0,date(m),date(d)+$Days,date(Y)));
			// 맴버 테이블에 생성 - 회원 가입
			$userip = getenv("REMOTE_ADDR");
			$Signup="INSERT INTO Member(idx, code, account, date, address) VALUES
			(NULL, '$InCode', '0', '$diff', '$userip')";
			mysqli_query($conn, $Signup);
			// 코드 삭제
			$CodeDel="DELETE FROM Code WHERE code='$InCode'";
			mysqli_query($conn, $CodeDel);
		}
	}	
	mysqli_free_result($m_query);
	mysqli_free_result($result);
	mysqli_close($conn);
}

/* 로그아웃 */
if (isset($_POST['Out']))
{
	$InCode = anti_SQL_Injection($_POST['Out']);

	// 코드 가져오기
	$check="SELECT * FROM Member WHERE code='$InCode'";
	$result=mysqli_query($conn, $check);
	$num_rows = mysqli_num_rows($result);
	$row=mysqli_fetch_array($result);

	$u_Code= $row['code'];

	$sql_add = "UPDATE Member SET account='0' WHERE code='$u_Code'";
	mysqli_query($conn, $sql_add);
	
	mysqli_free_result($result);
	mysqli_close($conn);
}

?>