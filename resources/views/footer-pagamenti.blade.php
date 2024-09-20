<tr style="padding:10px!important; text-align:center;">
    <td colspan="4" style="padding:10px!important; text-align:center;">
        <div class="flex" style="justify-content:space-evenly">
            <div class="bg-primary-600 rounded-xl shadow-xl" style="padding:10px; color:white;">
                <b>TOTALE COMANDA</b><br>
                <span style="font-size:1.2em">{{$comanda->totale_finale}} €</span>
            </div>
            <div class="bg-primary-600 rounded-xl shadow-xl" style="padding:10px; color:white;">
                <b>TOTALE PAGATO</b><br>
                <span style="font-size:1.2em">{{$comanda->totale_pagato}} €</span>
            </div>
            <div class="bg-gray-950 rounded-xl shadow-xl" style="padding:10px; @if ($comanda->totale_da_pagare > 0) color:red; @else color:green; @endif">
                <b>TOTALE ANCORA DA PAGARE:</b><br>
                <span style="font-size:1.2em">{{$comanda->totale_da_pagare}} €</span>
            </div>
        </div>
    </td>
</tr>
