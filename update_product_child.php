<?php
/*
Plugin Name: update_product_child
Plugin URI:
Description: Se ejecutara al momento de disparase el hook  woocommerce_update_product para actualizar todos lo productos hijos con el mismo nombre con el fin de editar los campo que se actualizaron en el producto padre y pasen a los productos hijos
Version:
Author:
Author URI:
License:
License URI:
*/
 

/*
global $wpdb;
echo 'debug true';
 */
add_action('woocommerce_update_product', 'my_product_update', 10, 2);

function my_product_update($product_id, $product)
{
    global $wpdb;
 
    //Se añade validar varaciones
    // Traer el producto por id
    $results = $wpdb->get_results("SELECT ps.ID, ps.post_title, ps.post_excerpt, ps.post_content, pm.meta_value FROM `as35co_posts` as ps INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id 
    WHERE  ps.ID = '$product_id' and pm.meta_key = '_sku' ");

 
    // Verificar resultados
    if (! empty($results)) {
        $name_product = '';
        $post_excerpt = '';
        $post_content = '';
        
        $sku_1 = '';
        foreach ($results as $p) {
            // Obtengo el nombre del producto editado
            $name_product = $p->post_title;
            $post_excerpt = $p->post_excerpt;
            $post_content = $p->post_content;
            $sku_1 = $p->meta_value;
        }
        
        $value_video_urna = '';
        $product_attributes = '';
        $product_weight = '';
        $value_img = '';
        $value_img_gallery = '';
        $value_ficha_tecnica = '';
        $value_ficha_tecnica2 = '';
        $value_manual_de_instalacion = '';
        $value_manual_de_instalacion2 = '';
        $value_resistencia_al_fuego = '';
        $value_resistencia_al_fuego2 = '';
        $value_autodeclaracion_ambiental = '';
        $value_autodeclaracion_ambiental2 = '';
        $value_garantia_acesco = '';
        $value_garantia_acesco2 = '';
        $value_upsell_ids = '';
        $value_crosssell_ids = '';

        $result_psmeta_img = $wpdb->get_results("SELECT * FROM `as35co_postmeta` WHERE post_id = '$product_id'");
        foreach ($result_psmeta_img as $psi) {
            if ($psi->meta_key === '_video_url') {
                $value_video_urna = $psi->meta_value;
            }
            if ($psi->meta_key === '_product_attributes') {
                $product_attributes = $psi->meta_value;
            }
            if ($psi->meta_key === '_weight') {
                $product_weight = $psi->meta_value;
            }
            if ($psi->meta_key === '_thumbnail_id') {
                $value_img = $psi->meta_value;
            }
            if ($psi->meta_key === '_product_image_gallery') {
                $value_img_gallery = $psi->meta_value;
            }
            if ($psi->meta_key === 'ficha_tecnica') {
                $value_ficha_tecnica = $psi->meta_value;
            }
            if ($psi->meta_key === '_ficha_tecnica') {
                $value_ficha_tecnica2 = $psi->meta_value;
            }
            if ($psi->meta_key === 'manual_de_instalacion') {
                $value_manual_de_instalacion = $psi->meta_value;
            }
            if ($psi->meta_key === '_manual_de_instalacion') {
                $value_manual_de_instalacion2 = $psi->meta_value;
            }
            if ($psi->meta_key === 'manual_de_resistencia_al_fuego') {
                $value_resistencia_al_fuego = $psi->meta_value;
            }
            if ($psi->meta_key === '_manual_de_resistencia_al_fuego') {
                $value_resistencia_al_fuego2 = $psi->meta_value;
            }
            if ($psi->meta_key === 'autodeclaracion_ambiental') {
                $value_autodeclaracion_ambiental = $psi->meta_value;
            }
            if ($psi->meta_key === '_autodeclaracion_ambiental') {
                $value_autodeclaracion_ambiental2 = $psi->meta_value;
            }
            if ($psi->meta_key === 'garantia_acesco') {
                $value_garantia_acesco = $psi->meta_value;
            }
            if ($psi->meta_key === '_garantia_acesco') {
                $value_garantia_acesco2 = $psi->meta_value;
            }
            if ($psi->meta_key === '_upsell_ids') {
                $value_upsell_ids = $psi->meta_value;
            }
            if ($psi->meta_key === '_crosssell_ids') {
                $value_crosssell_ids = $psi->meta_value;
            }
        }
        /*
        global $product;
        $product = wc_get_product( $product_id );
        $upsell_ids = $product->get_upsell_ids();
        $wpdb->update('as35co_postmeta', array('meta_value'=>$value_crosssell_ids), array('meta_id'=>9999999));
        */
        
     
        /**
        * Cambios para variaciones
        * Consultar las variaciones del producto padre y actualziarlas con las variaciones de los productos vendedores
        * 1. Consultar las variociones del producto padre
        * 2. Cunstlar los productos hijos
        *      - De cada producto hijo consultar las variaciones y compararlas con las variciones originales
        *      - Actualziar las variaciones
        */
        // Validar que sku no tenga guion para verificar que sea padre
        $search_hyphen_v = strpos($sku_1, "-");
        $sku_variation_master = null;
        if (!$search_hyphen_v) {
            // El producto  es padre el SKU posee guion
            /**
             * 1. Bsucar el producto padre
             * 2. Buscar variciones hijas por el Id del porducto editado
             * 3. Herdar SKU y peso
             */
            $get_master_product = $wpdb->get_results("SELECT ps.ID, ps.post_title,  ps.post_status, pm.meta_value FROM `as35co_posts` as ps 
                INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id
                WHERE  ps.post_title = '$name_product' and ps.post_type = 'product' and pm.meta_key = '_sku' order by Id asc limit 1");

            if (!empty($get_master_product)) {
                foreach ($get_master_product as $p_master) {
                    //Validar nuevamnete que el SKU no tenga guion
                    $search_hyphen_v = strpos($p_master->meta_value, "-");
                    $sku_variation_master = $p_master->meta_value;
                    if (!$search_hyphen_v) {
                        //Conusltar productos hijso del producto actual
                        $produts_child = $wpdb->get_results("SELECT ps.ID, ps.post_title, pm.meta_value FROM `as35co_posts` as ps 
                        INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id
                        WHERE ps.post_title = '$name_product' and ps.post_type = 'product'  and ps.post_status != 'trash' and pm.meta_key = '_sku'  and pm.meta_value LIKE '$p_master->meta_value-%'");
                        if (!empty($produts_child)) {
                            foreach ($produts_child as $p_child) {
                                //Conusltar variacioens hijas de cada producto hijo para heredar

                                $results_all_variations = $wpdb->get_results("SELECT ps.ID, ps.post_title,ps.post_excerpt, ps.post_parent, ps.post_type, pm.meta_value FROM `as35co_posts` as ps 
                                INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id 
                                WHERE ps.post_parent = $p_child->ID and ps.post_type = 'product_variation' group by ID");
                            
                                if (!empty($results_all_variations)) {
                                    foreach ($results_all_variations as $var_child) {
                                        // Consultar variacion hijas para heredar SKU y peso
                                        $result_variation_master = $wpdb->get_results(" SELECT ps.ID, ps.post_title,ps.post_excerpt, ps.post_parent, ps.post_type, ps.post_status, pm.meta_value FROM `as35co_posts` as ps 
                                        INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id 
                                        WHERE ps.post_parent = $p_master->ID and ps.post_type = 'product_variation' and post_excerpt = '$var_child->post_excerpt' and pm.meta_key = '_sku'  group by ID");
                                        foreach ($result_variation_master as $var_master) {
                                            // Conuutar peso y sku
                                            $variations_atr = $wpdb->get_results("SELECT * FROM `as35co_postmeta` WHERE post_id = $var_master->ID");
                                            $weight = '';
                                            $new_sku = $var_master->meta_value;
                                            foreach ($variations_atr as $item) {
                                                if ($item->meta_key === '_weight') {
                                                    $weight = $item->meta_value;
                                                }
                                            }
                                            update_post_meta($var_child->ID, '_weight', $weight);

                                            if ($var_master->post_status  === 'private') {
                                                $wpdb->update('as35co_posts', array('post_status'=> 'private' ), array('ID'=>$var_child->ID));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // El producto no es padre el SKU posee guion
            /**
             * 1. Bsucar el producto padre
             * 2. Buscar variciones hijas por el Id del porducto editado
             * 3. Herdar SKU y peso
             */
            $get_master_product = $wpdb->get_results("SELECT ps.ID, ps.post_title, pm.meta_value FROM `as35co_posts` as ps 
                INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id
                WHERE  ps.post_title = '$name_product' and ps.post_type = 'product' and pm.meta_key = '_sku' order by Id asc limit 1");

            if (!empty($get_master_product)) {
                foreach ($get_master_product as $p_master) {
                    //Validar nuevamnete que el SKU no tenga guion
                    $search_hyphen_v = strpos($p_master->meta_value, "-");
                    $sku_variation_master = $p_master->meta_value;
                    if (!$search_hyphen_v) {
                        //Conusltar variciones hijas del producto actual
                        $results_all_variations = $wpdb->get_results("SELECT ps.ID, ps.post_title,ps.post_excerpt, ps.post_parent, ps.post_type, pm.meta_value FROM `as35co_posts` as ps 
                            INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id 
                            WHERE ps.post_parent = $product_id and ps.post_type = 'product_variation' group by ID");
                        
                        if (!empty($results_all_variations)) {
                            foreach ($results_all_variations as $var_child) {
                                // Consultar variacion hija en el padre para heredar SKU y peso
                                $result_variation_master = $wpdb->get_results(" SELECT ps.ID, ps.post_title,ps.post_excerpt, ps.post_parent, ps.post_type, ps.post_status, pm.meta_value FROM `as35co_posts` as ps 
                                INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id 
                                WHERE ps.post_parent = $p_master->ID and ps.post_type = 'product_variation' and post_excerpt = '$var_child->post_excerpt' and pm.meta_key = '_sku'  group by ID");
                                foreach ($result_variation_master as $var_master) {
                                    // Conuutar peso y sku
                                    $variations_atr = $wpdb->get_results("SELECT * FROM `as35co_postmeta` WHERE post_id = $var_master->ID");
                                    $weight = '';
                                    $new_sku = $var_master->meta_value;
                                    foreach ($variations_atr as $item) {
                                        if ($item->meta_key === '_weight') {
                                            $weight = $item->meta_value;
                                        }
                                        if ($item->meta_key === '_sku') {
                                            $new_sku = $item->meta_value;
                                        }
                                    }
                                    // Generar SKU mas guion
                                    $conut_sku = $wpdb->get_results("SELECT count(*) as total FROM `as35co_postmeta` WHERE meta_key = '_sku' and meta_value = $sku_variation_master");
                                    if (!empty($conut_sku)) {
                                        foreach ($conut_sku as $num) {
                                            $new_sku = $new_sku.'-'.$num->total;
                                        }
                                    } else {
                                        $new_sku = $new_sku.'-1';
                                    }
                                    $sku_validation =  $wpdb->get_results("SELECT * FROM `as35co_postmeta` WHERE post_id = $var_child->ID and meta_key = '_sku'");
                                    if (empty($sku_validation)) {
                                        update_post_meta($var_child->ID, '_sku', $new_sku);
                                    }
                                    update_post_meta($var_child->ID, '_weight', $weight);
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        /*
        echo'<script type="text/javascript">
        alert("El producto no es padre");
        </script>';
        */
    }
    $search_hyphen_v = strpos($sku_1, "-");
    if (!$search_hyphen_v) {
        if (isset($name_product) && $name_product !== '') {
            // consultar los productos por nombre
            $results_all_products = $wpdb->get_results("SELECT ps.ID, ps.post_title, pm.meta_value FROM `as35co_posts` as ps 
            INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id
            WHERE ps.post_title = '$name_product' and ps.post_type = 'product' and pm.meta_key = '_sku'", OBJECT);
            
            if (!empty($results_all_products)) {
                foreach ($results_all_products as $p) {
                    // verificar el SKU con guin
                    if ($p->meta_value !== '') {
                        $sku = $p->meta_value;
                        $search_hyphen = strpos($sku, "-");
                        if ($search_hyphen>0) {
                            //Actualizar campos en tb post
                            $wpdb->update('as35co_posts', array('post_excerpt'=>$post_excerpt, 'post_content'=>$post_content), array('ID'=>$p->ID));
                            // actualziar campos en postmeta _product_attributes
                            $id_psmeta = $wpdb->get_results("SELECT * FROM `as35co_postmeta` WHERE post_id = $p->ID");
                            $meta_id = null; // Id en la tabla postmeta para editar
                            $meta_upsell_id = null;
                            $meta_crosssell_ids = null;
                            foreach ($id_psmeta as $ps) {
                                if ($ps->meta_key === '_product_attributes') {
                                    $meta_id = $ps->meta_id;
                                }
                                if ($ps->meta_key === '_upsell_ids') {
                                    $meta_upsell_id = $ps->meta_id;
                                }
                                if ($ps->meta_key === '_crosssell_ids') {
                                    $meta_crosssell_ids = $ps->meta_id;
                                }
                                if ($ps->meta_key === '_weight') {
                                    $product_weight = $ps->_weight;
                                }
                            }
                            if (empty($meta_id)) {
                                add_post_meta($p->ID, '_product_attributes', $product_attributes);
                            } else {
                                $wpdb->update('as35co_postmeta', array('meta_value'=>''.$product_attributes), array('meta_id'=>$meta_id));
                            }
                            if (empty($meta_upsell_id)) {
                                add_post_meta($p->ID, '_upsell_ids', $value_upsell_ids);
                            } else {
                                $wpdb->update('as35co_postmeta', array('meta_value'=>''.$value_upsell_ids), array('meta_id'=>$meta_upsell_id));// a:2:{i:0;i:15656;i:1;i:8761;}
                            }
                            if (empty($meta_crosssell_ids)) {
                                add_post_meta($p->ID, '_crosssell_ids', $value_crosssell_ids);
                            } else {
                                $wpdb->update('as35co_postmeta', array('meta_value'=>''.$value_crosssell_ids), array('meta_id'=>$meta_crosssell_ids));
                            }
    
                            // actualziar campos en postmeta _weight
                            update_post_meta($p->ID, '_weight', $product_weight);
                            //Adicion 16 Feb campos adcionales, imagen, galeria
                            update_post_meta($p->ID, '_thumbnail_id', $value_img);
                            // Heredar galeria de imagenes
                            update_post_meta($p->ID, '_product_image_gallery', $value_img_gallery);
                            // Heredar video urna
                            update_post_meta($p->ID, '_video_url', $value_video_urna);
                            /***
                             * Heredar manuales
                            
                            // Heredar ficha_tecnica
                            update_post_meta($p->ID, 'ficha_tecnica', $value_ficha_tecnica);
                            // field update
                            update_post_meta($p->ID, '_ficha_tecnica', $value_ficha_tecnica2);
                            // Heredar value_manual_de_instalacion
                            update_post_meta($p->ID, 'manual_de_instalacion', $value_manual_de_instalacion);
                            // field update
                            update_post_meta($p->ID, '_manual_de_instalacion', $value_manual_de_instalacion2);
                            // Heredar manual_de_resistencia_al_fuego
                            update_post_meta($p->ID, 'manual_de_resistencia_al_fuego', $value_resistencia_al_fuego);
                            // field update
                            update_post_meta($p->ID, '_manual_de_resistencia_al_fuego', $value_resistencia_al_fuego2);
                            // Heredar autodeclaracion_ambiental
                            update_post_meta($p->ID, 'autodeclaracion_ambiental', $value_autodeclaracion_ambiental);
                            // field update
                            update_post_meta($p->ID, '_autodeclaracion_ambiental', $value_autodeclaracion_ambiental2);
                            // Heredar garantia_acesco
                            update_post_meta($p->ID, 'garantia_acesco', $value_garantia_acesco);
                            // field update
                            update_post_meta($p->ID, '_garantia_acesco', $value_garantia_acesco2);
                             */
    
                         
                            // Heredar garantia_acesco
                            //update_post_meta($p->ID, '_upsell_ids', ''.$value_upsell_ids);
                            // field update
                            //update_post_meta($p->ID, '_crosssell_ids', ''.$value_crosssell_ids);
                            /*
                            $product2 = wc_get_product($p->ID);
                            if ($product2) {
                                $product2->set_upsell_ids(array(15656,8761));
                                // $product->set_cross_sell_ids(array(4, 5, 6));
                                $product2->save();
                            }
                            */
                            /**
                             * Actualizar TB  as35co_term_relationships
                             * Las taxonomias pueden ser diferentes en los ambientes y generar error
                             * Taxonomias faltanates para inluir en PRO:
                             * - Dimensiones (cm)
                             * - Geometría
                             * Taxonomias a quitar:
                             * - pa_dimensiones_pulgadas
                             * - pa_marca
                             * - pa_peso
                             * - pa_dimensiones-pulgadas
                             */
                            $taxonomys = array('product_tag','product_cat', 'pa_color', 'pa_ancho-mm','pa_recubrimiento-g', 'pa_unidad-de-empaque',  'pa_espesor-mm', 'pa_largo-mm', 'pa_calibre', 'pa_dimensiones-mm', 'pa_dimensiones-pulgadas');
                            
                            $term_relationships_father = wp_get_object_terms(array($product_id), $taxonomys);
    
    
                            $id_temr = array();
                            $ids_product_tags = array();
                            $ids_colors = array();
                            $ids_pa_ancho_mm = array();
                            $ids_pa_espesor_mm= array();
                            $ids_pa_largo_mm= array();
                            $ids_pa_calibre= array();
                            $ids_pa_dimensiones= array();
                            $ids_pa_dimensiones_mm= array();
                            $ids_pa_dimensiones_pulgadas= array();
                            $ids_pa_geometria= array();
                            $ids_pa_marca= array();
                            $ids_pa_peso= array();
                            $ids_pa_recubrimiento_g= array();
                            $ids_pa_unidad_de_empaque= array();
                            foreach ($term_relationships_father as $i) {
                                if ($i->taxonomy === 'product_cat') {
                                    /**
                                     * El Id de la categoria cambia segun el ambiente
                                     */
                                    if ($i->term_taxonomy_id  !== 384) {
                                        array_push($id_temr, (int)$i->term_taxonomy_id);
                                    }
                                }
                                if ($i->taxonomy === 'product_tag') {
                                    array_push($ids_product_tags, (int)$i->term_taxonomy_id);
                                }
                                if ($i->taxonomy === 'pa_color') {
                                    array_push($ids_colors, (int)$i->term_taxonomy_id);
                                }
                                if ($i->taxonomy === 'pa_ancho-mm') {
                                    array_push($ids_pa_ancho_mm, (int)$i->term_taxonomy_id);
                                }
                                if ($i->taxonomy === 'pa_espesor-mm') {
                                    array_push($ids_pa_espesor_mm, (int)$i->term_taxonomy_id);
                                }
                                if ($i->taxonomy === 'pa_largo-mm') {
                                    array_push($ids_pa_largo_mm, (int)$i->term_taxonomy_id);
                                }
                                if ($i->taxonomy === 'pa_calibre') {
                                    array_push($ids_pa_calibre, (int)$i->term_taxonomy_id);
                                }
                              
                                if ($i->taxonomy === 'pa_dimensiones-mm') {
                                    array_push($ids_pa_dimensiones_mm, (int)$i->term_taxonomy_id);
                                }
                                if ($i->taxonomy === 'pa_dimensiones-pulgadas') {
                                    array_push($ids_pa_dimensiones_pulgadas, (int)$i->term_taxonomy_id);
                                }
                                
                                if ($i->taxonomy === 'pa_recubrimiento-g') {
                                    array_push($ids_pa_recubrimiento_g, (int)$i->term_taxonomy_id);
                                }
                                if ($i->taxonomy === 'pa_unidad-de-empaque') {
                                    array_push($ids_pa_unidad_de_empaque, (int)$i->term_taxonomy_id);
                                }
                                /**
                                 * Categorias no resentes en PRO pero si en stage
                                 */
                                //if ($i->taxonomy === 'pa_dimensiones-pulgadas') {
                               //     array_push($ids_pa_dimensiones_pulgadas, (int)$i->term_taxonomy_id);
                                //}
                            }
                            wp_set_object_terms($p->ID, $id_temr, 'product_cat', $append = false);
                            wp_set_object_terms($p->ID, $ids_product_tags, 'product_tag', $append = false);
                            wp_set_object_terms($p->ID, $ids_colors, 'pa_color', $append = false);
                            wp_set_object_terms($p->ID, $ids_pa_ancho_mm, 'pa_ancho-mm', $append = false);
                            wp_set_object_terms($p->ID, $ids_pa_espesor_mm, 'pa_espesor-mm', $append = false);
                            wp_set_object_terms($p->ID, $ids_pa_largo_mm, 'pa_largo-mm', $append = false);
                            wp_set_object_terms($p->ID, $ids_pa_calibre, 'pa_calibre', $append = false);
                            wp_set_object_terms($p->ID, $ids_pa_dimensiones_mm, 'pa_dimensiones-mm', $append = false);
                            wp_set_object_terms($p->ID, $ids_pa_dimensiones_pulgadas, 'pa_dimensiones-pulgadas', $append = false);
                            wp_set_object_terms($p->ID, $ids_pa_recubrimiento_g, 'pa_recubrimiento-g', $append = false);
                            wp_set_object_terms($p->ID, $ids_pa_unidad_de_empaque, 'pa_unidad-de-empaque', $append = false);
                        }
                    }
                    // update_2($product_id, $p->ID);
                }
            } else {
            }
        } else {
            // En caso de no devoler resultados
            /*
            echo'<script type="text/javascript">
            alert("No se pudo actualizar los productos");
            </script>';*/
        }
    }
 
}
add_action('save_post', 'update_files', 10, 3);

 
function update_files($product_Id)
{
    global $wpdb;

    $results = $wpdb->get_results("SELECT ps.ID, ps.post_title, ps.post_excerpt, ps.post_content, pm.meta_value FROM `as35co_posts` as ps INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id 
    WHERE  ps.ID = '$product_Id' and pm.meta_key = '_sku' ");
    // Verificar resultados
    if (! empty($results)) {
        $name_product = '';
        $sku_12 = '';
        $id = '';
        foreach ($results as $p) {
            // Obtengo el nombre del producto editado
            $name_product = $p->post_title;
            $sku_12 = $p->meta_value;
            $id = $p->ID;
        }
        $search_hyphen = strpos($sku_12, "-");
        if (!$search_hyphen) {
          

            $value_ficha_tecnica = '';
            $value_ficha_tecnica2 = '';
            $value_manual_de_instalacion = '';
            $value_manual_de_instalacion2 = '';
            $value_resistencia_al_fuego = '';
            $value_resistencia_al_fuego2 = '';
            $value_autodeclaracion_ambiental = '';
            $value_autodeclaracion_ambiental2 = '';
            $value_garantia_acesco = '';
            $value_garantia_acesco2 = '';
   

            $result_psmeta_img = $wpdb->get_results("SELECT * FROM `as35co_postmeta` WHERE post_id = $id");

            foreach ($result_psmeta_img as $psi) {
                if ($psi->meta_key === 'ficha_tecnica') {
                    $value_ficha_tecnica = $psi->meta_value;
                }
                if ($psi->meta_key === '_ficha_tecnica') {
                    $value_ficha_tecnica2 = $psi->meta_value;
                }
                if ($psi->meta_key === 'manual_de_instalacion') {
                    $value_manual_de_instalacion = $psi->meta_value;
                }
                if ($psi->meta_key === '_manual_de_instalacion') {
                    $value_manual_de_instalacion2 = $psi->meta_value;
                }
                if ($psi->meta_key === 'manual_de_resistencia_al_fuego') {
                    $value_resistencia_al_fuego = $psi->meta_value;
                }
                if ($psi->meta_key === '_manual_de_resistencia_al_fuego') {
                    $value_resistencia_al_fuego2 = $psi->meta_value;
                }
                if ($psi->meta_key === 'autodeclaracion_ambiental') {
                    $value_autodeclaracion_ambiental = $psi->meta_value;
                }
                if ($psi->meta_key === '_autodeclaracion_ambiental') {
                    $value_autodeclaracion_ambiental2 = $psi->meta_value;
                }
                if ($psi->meta_key === 'garantia_acesco') {
                    $value_garantia_acesco = $psi->meta_value;
                }
                if ($psi->meta_key === '_garantia_acesco') {
                    $value_garantia_acesco2 = $psi->meta_value;
                }
            }
            if (isset($name_product) && $name_product !== '' && !$search_hyphen) {
                // consultar los productos por nombre
                $results_all_products = $wpdb->get_results("SELECT ps.ID, ps.post_title, pm.meta_value FROM `as35co_posts` as ps 
                INNER JOIN as35co_postmeta as pm ON ps.ID = pm.post_id
                WHERE ps.post_title = '$name_product' and ps.post_type = 'product' and pm.meta_key = '_sku'", OBJECT);
                         

                if (!empty($results_all_products)) {
                    foreach ($results_all_products as $p) {
                        // verificar el SKU con guin
                        if ($p->meta_value !== '') {
                            $sku = $p->meta_value;
                            $search_hyphen = strpos($sku, "-");
                            if ($search_hyphen>0) {
                                //Actualizar campos en tb post
                               
                                // actualziar campos en postmeta _product_attributes
                              
                                /***
                                 * Heredar manuales
                                 */
                                // Heredar ficha_tecnica
                                update_post_meta($p->ID, 'ficha_tecnica', $value_ficha_tecnica);
                                // field update
                                update_post_meta($p->ID, '_ficha_tecnica', $value_ficha_tecnica2);
                                // Heredar value_manual_de_instalacion
                                update_post_meta($p->ID, 'manual_de_instalacion', $value_manual_de_instalacion);
                                // field update
                                update_post_meta($p->ID, '_manual_de_instalacion', $value_manual_de_instalacion2);
                                // Heredar manual_de_resistencia_al_fuego
                                update_post_meta($p->ID, 'manual_de_resistencia_al_fuego', $value_resistencia_al_fuego);
                                // field update
                                update_post_meta($p->ID, '_manual_de_resistencia_al_fuego', $value_resistencia_al_fuego2);
                                // Heredar autodeclaracion_ambiental
                                update_post_meta($p->ID, 'autodeclaracion_ambiental', $value_autodeclaracion_ambiental);
                                // field update
                                update_post_meta($p->ID, '_autodeclaracion_ambiental', $value_autodeclaracion_ambiental2);
                                // Heredar garantia_acesco
                                update_post_meta($p->ID, 'garantia_acesco', $value_garantia_acesco);
                                // field update
                                update_post_meta($p->ID, '_garantia_acesco', $value_garantia_acesco2);
        
        
                            }
                        }
                        // update_2($product_id, $p->ID);
                    }
                } else {
                }
            }
        }
      
    }
}

add_action('woocommerce_update_product_variation', 'action_woocommerce_save_product_variation', 10, 1);

function action_woocommerce_save_product_variation($product_id)
{
    delete_variations($product_id);
}

/**
 * Desabilitar las varciones no permitidas de un producto
 */
function delete_variations($product_id)
{
    global $wpdb;

    // $wpdb->update('as35co_postmeta', array('meta_value'=>'id: '.$product_id), array('meta_id'=>226425));

    $get_product = $wpdb->get_results("SELECT * FROM `as35co_posts` WHERE ID = $product_id");
    if (!empty($get_product)) {
        $name_product = null;
        $Id_master = null;
        foreach ($get_product as $pro) {
            $name_product = $pro->post_title;
        }
        if ($name_product !== null) {
            // Conusltar el padre por nombre del product_id
            $get_product_master = $wpdb->get_results("SELECT * FROM `as35co_posts` WHERE post_title = '$name_product' and post_type = 'product_variation'  and post_parent != 0  ORDER BY post_parent ASC limit 1");
            // $wpdb->update('as35co_postmeta', array('meta_value'=>"SELECT * FROM `as35co_posts` WHERE post_title = '$name_product' and post_type = 'product_variation'  and post_parent != 0  ORDER BY post_parent ASC limit 1"), array('meta_id'=>226425));


            if (!empty($get_product_master)) {
                foreach ($get_product_master as $pro_master) {
                    $Id_master = $pro_master->post_parent;
                }
                // traer variciones del master
                $get_variations_master = $wpdb->get_results("SELECT * FROM `as35co_posts` WHERE  post_parent = $Id_master and post_type = 'product_variation'");

                // traer variaciones hijas - h1235987
                //var_dump('get_variations_master');
                //var_dump(json_encode($get_variations_master));
                $find_child;
                $encontro = '';
               
                $get_variations_child = $wpdb->get_results("SELECT * FROM `as35co_posts` WHERE ID = $product_id");

                $i = 1;
                $show_alert = false;

                if (!empty($get_variations_child)) {
                    foreach ($get_variations_child as $var_child) {
                        $find_child = false;
                        $i = $i +1;
                        $find_variation = $wpdb->get_results("SELECT * FROM `as35co_posts` WHERE post_parent = $Id_master and post_type = 'product_variation' and post_excerpt = '$var_child->post_excerpt'");
                        $sql = "SELECT * FROM `as35co_posts` WHERE post_parent = $Id_master and post_type = 'product_variation' and post_excerpt = '$var_child->post_excerpt'";
                        $wpdb->update('as35co_postmeta', array('meta_value'=> $sql.' - '.$product_id), array('meta_id'=>226425));
                        if ($var_child->post_excerpt !== '') {
                            if (!empty($find_variation)) {
                                //
                            } else {
                                $show_alert = true;
                                $wpdb->update('as35co_posts', array('post_status'=> 'private' ), array('ID'=>$var_child->ID));
                            }
                        }else{
                         }
                    }
                }else{
                    $show_alert = true;
                    $wpdb->update('as35co_posts', array('post_status'=> 'private' ), array('ID'=>$var_child->ID));
                }
                echo '<script type="text/javascript">
                        setTimeout(function(){ 
                            var element = document.getElementById("alertvar");
                            element.parentNode.removeChild(element); 
                         }, 6000);
                       
                     </script>';
                if ($show_alert) {
                    // show_message("Se inhabilitaron algunas variaciones no permitidas.");
                    echo '
                    <div id="alertvar" style="margin-bottom:5px;  z-index:10;padding: 20px;background-color: #f44336;color: white;  position: fixed;
                        bottom: 75px;
                        left: 20px;">
                        <span style=" margin-left: 15px;
                        color: white;
                        font-weight: bold;
                        float: right;
                        font-size: 22px;
                        line-height: 20px;
                        cursor: pointer;
                      
                        transition: 0.3s;" onclick="this.parentElement.style.display=\'none\';">&times;</span> 
                        <strong>Alerta!</strong> Se inhabilitaron algunas variaciones no permitidas.
                  </div>
                    ';
                }
            } else {
                // es primera variacion
            }
        }
    }
}
?>
