<?php


// @todo create a language file and consistently localize everything

function bluem_get_idin_logo_html()
{
    return "<img src='".
        plugin_dir_url(__FILE__)."assets/bluem/idin.png' class='bluem-idin-logo'
        style='float:left; max-height:64px; margin:10pt; margin-right:20pt;'/>";
}

// @todo make a stylesheet and include it, move all inline styles there.

function bluem_get_bluem_logo_html() {
    return '<img src="'.
    plugin_dir_url(__FILE__).'assets/bluem/logo.png'.
    '" class="bluem-bluem-logo" style="float:left; max-height:64px; margin:10pt;"/>';
}


function bluem_render_request_table($requests, $users_by_id=[])
{
    if(count($requests)==0) {
        echo "<p>".__("Nog geen verzoeken",'bluem')."</p>";
        return;
    }
    ?>
<table class="table widefat">

<thead>
    <tr>
    <th>Gebruiker</th>
    <th>Verzoek</th>
    <th>Datum</th>
    <th>Extra informatie</th>
    <th>Status</th>
    <th></th>
    </tr>
</thead>
<tbody>


<?php foreach ($requests as $r) {
        ?>
<tr>


    <td>
    <?php 
    if (isset($users_by_id[(int)$r->user_id])) {
            ?>
<a href="<?php echo admin_url("user-edit.php?user_id=".$r->user_id); ?>" target="_blank">
        <?php
        echo $users_by_id[(int)$r->user_id]->user_nicename; ?>
        </a>

        <?php
        } else {
            echo "Gast/onbekend";
        } ?>
    </td>
    <td>
    <?php echo $r->description; ?>
    <br>
    <span style="color:#aaa; font-size:9pt;">
    <?php echo $r->transaction_id; ?>
    <br>
    <?php if(isset($r->debtor_reference) && $r->debtor_reference !=="") {
        echo "Klantreferentie: ".$r->debtor_reference; 
    } ?>
    </span>
    </td>
    <td>
        <?php $rdate = strtotime($r->timestamp); ?>
        <?php echo date("d-m-Y H:i:s", $rdate); ?>

    </td>
    <td>
    <?php
        if (!is_null($r->order_id)) { 
            
            $order = new \WC_Order($r->order_id); 		
            
            ?>
            <a href="<?php echo admin_url("post.php?post={$r->order_id}&action=edit"); ?>" target="_blank">
            Order <?php echo $r->order_id ?> (<?php echo wc_price($order->get_total());?>)
            </a><?php
        } ?>
    </td>
    <td>
    <?php bluem_render_request_status($r->status); ?>
    </td>
    <td></td>
</tr>


    <?php
    } ?>

</tbody>
</table>
<?php
}


function bluem_render_request_status($status)
{
    switch (strtolower($status)) {
        case 'created':
            {
                echo "<span style='color:#1a94c0;'>
                <span class='dashicons dashicons-plus-alt'></span>
                    Aangemaakt
                </span>";

                break;
            }

            case 'success':
                {
                    echo "<span style='color:#2e9801'>
                    
                <span class='dashicons dashicons-yes-alt'></span>
                        Succesvol afgerond
                    </span>";

                    break;
                }

                case 'cancelled':
                    {
                        echo "<span style='color:#bd1818'>
                    
                        <span class='dashicons dashicons-dismiss'></span>
                        Geannuleerd</span>";
                        break;
                    }
                case 'expired':
                    {
                        echo "<span style='color:#bd1818'>
                    
                        <span class='dashicons dashicons-dismiss'></span>
                        Verlopen</span>";
                    break;
                    }
                case 'open':
                case 'new':
                    {
                        echo "<span style='color:#6a4285'>
                    
                        <span class='dashicons dashicons-editor-help'></span>
                        Openstaand</span>";
                    break;
                    }
                case 'pending':
                    {
                        echo "<span style='color:#6a4285' title='mogelijk moet dit verzoek nog worden ondertekend door een tweede ondertekenaar'>
                    
                        <span class='dashicons dashicons-editor-help'></span>
                        in afwachting van verwerking</span>";
                    break;
                    }
                case 'processing':
                    {
                        echo "<span style='color:#6a4285'>
                    
                        <span class='dashicons dashicons-marker'></span>
                        In verwerking</span>";
                    break;
                    }
            
                
                
                
                
                
                
                default:
                {
echo $status;
                    break;
                }
    }
}
