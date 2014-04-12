<?
/*
	����� ��� ������� � ����� ������ ������ ����������� ��������������. ��� SEO.
	������: ����� ����� �� ������������ ������� � �������� ��� �� ������.
	��������: ����� ����� "����*", ���� ����� ��� ���� ����� "��������*", �� ���� ��� 2 ����� � ����� ����� ����.
	����� �������, �������� �������������� "������ ���������� ����", "�� ���������� ������" � �.�.

	������� ������ � ������� substr_replace � �� str_replace:
	������ ���������� �� ��� ��������� - � �������, � ��������� title � alt ������ �� ����� �� ��������� ��������.
	������� ������� ����� ����������� �� ������� � �� ���������, � ������� ������ �� �����. ��� ���������� ��� ���������� ������.
	� ����� ������ ������ ��������� ��� ������ �� ������. ������������ �� ������� � �� ��� ��� �������� � �������� ������, 
	�� ���������� ��������, � ������� ������ �� �����.

	�.�. ����� �� ���������. �� �� ���� ������� ��������� ���������. ���� ����������.

*/
class ReplaceText {
	 public function getDelSpecCh($text) {
        $text = strtr($text, array( ':' => '', '}' => '', '"' => '', '{' => '', "(" => "", ")" => "", 
                                    '&' => '', '$' => '', '!' => '', '#' => '', '�' => '', ';' => '', '%' => '', '^' => '', 
                                    '?' => '', '*' => '', '=' => '', '+' => '', '[' => '', ']' => '', '/' => '', '>' => '', 
                                    '<' => '', ',' => '', '|' => '', "'" => "", "_" => "", "." => ''));
        return $text;
    }

    // ����� �������� � ������ ���� (�� ���� ����������) �� ������� $tagarr � ��������� alt � title ����� (����) �� ����. 
    public function replaseZero($text) {
        // error_log("INPUT");
        // error_log($text);
        $tagarr = array('a', 'h1');
        $str = '';
        preg_match_all("/(<([\w]+)[^>]*>)(.*?)(<\/\\2>)/", $text, $searchtag, PREG_SET_ORDER);
        preg_match_all('/(alt|title)=((\'|")[^(\'|")]*(\'|"))/i', $text, $titlealt, PREG_SET_ORDER);

        foreach ($searchtag as $tag) {
            if (in_array($tag[2], $tagarr)){
                $str = str_pad(0, strlen($tag[0]), '0');
                $text = str_replace($tag[0], $str, $text);
            }
        }

        foreach ($titlealt as $meta) {
            $str = str_pad(0, strlen($meta[0]), '0');
            $text = str_replace($meta[0], $str, $text);
        }
        // error_log("OUTPUT");
        // error_log($text);
        return $text;
    }

    /**
	*	����� ������ ������ �� ������
	*	@param string $text - �����, � ������� ����� ������������� ������.
	*	@param array $word - ������ ����, �� ������� ���������� ������ ���������� �����. ������ ����� �������� �������� - ����� ����� �������� � ����.
	*	���� ������� �� ������, �� ���������� ����� ����� ��������� ����� ������.
	*	@param int $countword - ���������� ����, ������� ����� � ����� ������. �� ����� ���� ������ ������.
	*	@param string $url - ������, �� ������� ����� ��������� ������
	*	@param int $side - � ����� ������� �� ��������� (�������) ����� ����������� ����� �����������. -1 - �����(� ������� ������ ������). 1(�� ���������) - ������.
	*	@return string ��������� �����
	*/
    public function addLink($text, $word, $countword, $url, $side = 1) {
        $arrword = array();
        // ���� ��������� �����������, ������ ���������� ����� �������, ��� ���������
        if (!is_string($text) or !is_int($countword) or $countword < 1 or abs($side) != 1 or $url == '' or !admin_id){
            return $text;
        }
        // ���� �����(�����) ������ ������� ���� ���� ����� ����������
        if (!is_array($word)) {
            $arrword[] = $word;
        } else {
            $arrword = $word;
        }
        // ���� � ������ ����������� ������ ����� �� ������� (��������), �� �������� ������
        if (strstr($text, $arrword[0])){
                // ������ �� ����. ��� ������� ��� ����, ��� � �������, � ���������� ��� � ���������� title �� ������ ����� �� ������.
                $newtext = $this->replaseZero($text);
                $explval = explode(' ', $newtext);
                $seapos = array();
                // ���������� ���������� ������ ����, ��������� ����������� ������ �� ��������
                foreach ($explval as $key => $oneword) {
                    // ���� �����, ������� ���������� �� �����, ����������� ������ � ������� ���� ��� ������
                    if(substr($oneword, 0, strlen($arrword[0])) == $arrword[0]) {
                        $ok = true;
                        $tempstr = '';
                        // ���������, ��������� �� ����������� (����������) ����� � ������ �� �������, ����������� � ������� ��� ������
                        for ($i = 1; $i < count($arrword); $i++) {
                            $ok*= substr($explval[($key + $i * $side)], 0, strlen($arrword[$i])) == $arrword[$i];
                        }
                        if ($ok) {
                            // �������� ����� ��� ������, ���� �� ������ ���������� ��������� � ������� ������
                            // TO DO: ��������� ������������ �������� ���������� ������ � ����� � ��������� �� ����������.
                            if ($side < 0) {                                
                                for ($i = $countword - 1; $i > 0; $i--) { 
                                    $tempstr.= $explval[($key + $i * $side)] . ' ';
                                }
                                $tempstr.= $this->getDelSpecCh(strip_tags($oneword));
                            } else {
                                $tempstr.= $this->getDelSpecCh(strip_tags($oneword));
                                for ($i = 1; $i < $countword; $i++) { 
                                    $tempstr.= ' ' . $explval[($key + $i * $side)];
                                }
                            }
                            $searchstring = $tempstr;
                            $n = 0;
                            // �������� ������� � ������ ����, ������� ��������� ������. ����� � ������ {������� => �����}
                            while (strpos($newtext, $searchstring, $n) !== false) {

                                $temp = strpos($newtext, $searchstring, $n);
                                // ���������, ���� �� ���� ������� ��� �� ������� �������� ��� ���� ��������� ������� ������ �� �����
                                if (!$seapos[$temp] or strlen($seapos[$temp] < $searchstring)) {
                                	$seapos[$temp] = $searchstring;
                                }
                                $n = $temp + strlen($searchstring);

                            }
                        }
                    }
                }
            // ���� �� ��������� � ��������, ���������� �������� ����� � ������ � ����� ������. ��� ����� ����� ������� (�������) ��������� � ������� ����������
            krsort($seapos);
            // ������ ��������� ���� �� ������
            foreach ($seapos as $key => $value) {
                $text = substr_replace($text, '<a href="' . $url . '">' . $value . '</a>', $key, strlen($value));                
            }
        }
        return $text;
    }
}

?>
