<?php

class Modules_XMLSimpleForm {

	public function renderNode($node, $settings, $xpath='') {

		$data = '';
		$children = $settings->XPath()->query("./*", $node);

		if($node->getAttribute('type') == '' && $children->length > 0) {
			$node->setAttribute('type', 'group');
		}

		$label = $node->getAttribute('label') != '' ? $node->getAttribute('label') : ucfirst($node->localName);
		$label = __($label);

		$xpath .= '/' . $node->localName;

		switch($node->getAttribute('type')) {

			case 'group':

				$data .= '<fieldset><legend>';
				$data .= $label;
				$data .= '</legend>';
				if($children->length > 0) {
					foreach($children AS $childNode) {
						$data .= $this->renderNode($childNode, $settings, $xpath);
					}
				}
				$data .= '</fieldset>';
				break;


			case 'email':

				$data .= '<p>';
				$data .= '<label for="data[' . $xpath . ']">' . $label . '</label>';
				$data .= '<input type="email" id="data[' . $xpath . ']" name="data[' . $xpath . ']" value="' . $node->textContent . '" />';
				$data .= '</p>';
				break;


			case 'checkbox':

				$data .= '<p>';
				$data .= '<label for="data[' . $xpath . ']">';
				$checked = ($node->textContent == 1) ? ' checked="checked" ' : '';
				$data .= '<input type="checkbox" id="data[' . $xpath . ']" name="data[' . $xpath . ']" value="1" ' . $checked . ' /> ';
				$data .= $label . '</label>';
				$data .= $node->getAttribute('break') == 'true' ? '<br />' : '';
				$data .= '</p>';
				break;


			case 'range':

				$data .= '<p>';
				$data .= '<label for="data[' . $xpath . ']">' . $label . '</label>';
				$data .= '<input type="range" min="' . (int) $node->getAttribute('min') . '" max="' . (int) $node->getAttribute('max') . '" id="data[' . $xpath . ']" name="data[' . $xpath . ']" value="' . $node->textContent . '" />';
				$data .= '</p>';
				break;


			case 'select':

				$data .= '<p>';
				$data .= '<label for="' . $node->localName . '">' . $label . '</label>';
				$data .= '<select name="data['.$xpath.']">';

				if($node->getAttribute('list') != '') {
					$items = explode(';', $node->getAttribute('list'));
					foreach($items AS $item) {
						$values = explode('=', $item, 2);
						$value = $values[0];
						$text = $values[1];
						$selected = $value == $node->textContent ? ' selected="selected" ' : '';
						$data .= '<option ' . $selected . ' value="' . $value . '">' . $text . '</option>';
					}
				}

				if($node->getAttribute('callback') != '') {

					preg_match("/(.*)(::|->)(.*)\((.*)?\)/Usm", $node->getAttribute('callback'), $callback);
					if(!empty($callback)) {
						$object = $callback[1];
						$method = $callback[3];
						if(isset($callback[4])) {
							$params = explode(',', $callback[4]);
							foreach($params AS $i => $param) {
								$params[$i] = trim($param, " \t'\"");
							}
						}

						if(is_callable(array($object, $method))) {
							$params = is_array($params) ? $params : array($params);
							$values = call_user_func_array(array(new $object, $method), $params);
						}

						foreach($values AS $key => $value) {
							$selected = $key == $node->textContent ? ' selected="selected" ' : '';
							$data .= '<option ' . $selected . ' value="' . $key . '">' . $value . '</option>';
						}

					}

				}
				$data .= '</select>';
				break;


			case 'text':
			default:
				$data .= '<p>';
				$data .= '<label for="data[' . $xpath . ']">' . $node->getAttribute('label') . '</label>';
				$data .= '<input type="text" id="data[' . $xpath . ']" name="data[' . $xpath . ']" value="' . $node->textContent . '" />';
				$data .= '</p>';

		}

		return $data;

	}

}