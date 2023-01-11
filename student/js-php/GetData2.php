<?php

	$x1=file_get_contents('../../assets/json_data/openStax.json');
	$x2=file_get_contents('../../assets/json_data/static_questions_id.json');
	$x3=file_get_contents('../../assets/json_data/static_questions_info.json');
	$x4=file_get_contents('../../assets/json_data/Kmap.json');
	
	$array = ['ChapterList' => $x1,
			  'StaticQuestionsID' => $x2 ,
			  'StaticQuestionsInfo' => $x3,
			  'kMap' => $x4];
	echo json_encode($array);

?>