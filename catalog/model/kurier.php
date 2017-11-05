<?php
class ModelExtensionShippingKurier extends Model {
	public function getQuote($address) {
		$this->load->language('extension/shipping/kurier');

		$quote_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

		foreach ($query->rows as $result) {
			if ($this->config->get('shipping_kurier_' . $result['geo_zone_id'] . '_status')) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				if ($query->num_rows) {
					$status = true;
				} else {
					$status = false;
				}
			} else {
				$status = false;
			}

			if ($status) {
				$cost = '';
				$weight = $this->cart->getWeight();

				$rates = explode(',', $this->config->get('shipping_kurier_' . $result['geo_zone_id'] . '_rate'));

				foreach ($rates as $rate) {
					$data = explode(':', $rate);

					if ($data[0] >= $weight) {
						if (isset($data[1])) {
                                                        if($this->config->get('shipping_free_total') > $this->cart->getTotal()){
                                                            $cost = $data[1];
                                                        }else{
                                                           $cost = '00.00';
                                                        }
						}

						break;
					}
				}

				if ((string)$cost != '') {
					$quote_data['kurier_' . $result['geo_zone_id']] = array(
						'code'         => 'kurier.kurier_' . $result['geo_zone_id'],
						'title'        => $result['name'] . '  (' . $this->language->get('text_kurier') . ' ' . $this->weight->format($weight, $this->config->get('config_kurier_class_id')) . ')',
						'cost'         => $cost,
						'tax_class_id' => $this->config->get('shipping_kurier_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_kurier_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
					);
				}
			}
		}

		$method_data = array();

		if ($quote_data) {
			$method_data = array(
				'code'       => 'kurier',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_kurier_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}
