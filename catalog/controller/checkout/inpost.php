<?php

class ControllerCheckoutInpost extends Controller {

    public function index() {
        $data = array();
        /**
         * jesli nie ma juz pobranych paczkomatów pobierany cala liste
         * 
         */
        $content = file_get_contents('http://api.paczkomaty.pl/?do=listmachines_xml');

        $data_xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);


        /**
         * przetasowanie xml do array(
         *                         wojewodzcto => array(
         *                                      miasto => array(        
         *                                          postcode,
         *                                          street,
         *                                          buildingnumber,
         *                                          latitude,
         *                                          longitude)
         *                                      )
         *                          )
         * 
         */
        foreach ($data_xml->machine as $machine) {
            $data['machine'][reset($machine[0]->province)][reset($machine[0]->town)][] = array(
                reset($machine[0]->town),
                reset($machine[0]->postcode),
                reset($machine[0]->street),
                reset($machine[0]->buildingnumber),
                reset($machine[0]->latitude),
                reset($machine[0]->longitude)
            );
        }






        if (isset($_POST['box']) && !empty($_POST['box'])) {
            $geo = explode('|', $_POST['box']);

            if (count($geo) == 2 && isset($data['machine'][$geo[0]][$geo[1]])) {
                $json['newbox'] = $data['machine'][$geo[0]][$geo[1]];


                $view = $this->load->view('checkout/mapa', $json);
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($view));
            } else {
                $json['msg'] = 'Błędny parametr...';
                echo $this->load->view('checkout/mapa', $json);
            }
        } else {
            $data['msg'] = 'Wybierz swoje miasto';
        }
//        var_dump('test');exit;

        return $this->load->view('checkout/inpost', $data);
    }

    public function view() {

        $view = $this->load->controller('checkout/inpost');
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($view));
    }
    
    public function newaddress(){
       
        
        $this->session->data['shipping_address']['city'] = htmlspecialchars($_POST["miasto"]);
        $this->session->data['shipping_address']['postcode'] = htmlspecialchars($_POST["kod"]);
        $this->session->data['shipping_address']['address_1'] = htmlspecialchars($_POST["ulica"]);
        
       echo '1';
        
    }
}
