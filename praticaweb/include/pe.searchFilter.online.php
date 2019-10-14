                <input type="hidden" datatable="pe.avvioproc" id="op_pe-avvioproc-online" class="search text" name="online" value="equal">
                <input type="hidden" value="1" id="1_pe-avvioproc-online" name="online" class="text">
<!--                <table id="table-filter">
                   <tr id="tr-assegnata_istruttore">
                        <td valign="middle">
                            <label for="assegnata_istruttore" class="title">Istruttore assegnato</label><br/>
                            <input type="hidden" datatable="pe.vista_assegnate" id="op_pe-vista_assegnate-assegnata_istruttore" class="search text check" name="assegnata_istruttore" value="equal">                           
                            <input type="radio" value="0" id="1_pe-vista_assegante-assegnata_istruttore" name="assegnata_istruttore"  data-plugins="dynamic-search">
                            <label for="1_pe-vista_assegante-assegnata_istruttore" class="value">No</label><br/>
                            <input type="radio" value="1" id="1_pe-vista_assegante-assegnata_istruttore" name="assegnata_istruttore"  data-plugins="dynamic-search">
                            <label for="2_pe-vista_assegante-assegnata_istruttore" class="value">SI</label><br/>
                            <input type="radio" value="" id="1_pe-vista_assegante-assegnata_istruttore" name="assegnata_istruttore"  data-plugins="dynamic-search">
                            <label for="3_pe-vista_assegante-assegnata_istruttore" class="value">Tutte</label><br/>
                        </td>
                    </tr>
                    <tr id="tr-sportello">
                        <td valign="middle">
                            <label for="sportello" class="title">Sportello di Presentazione</label><br/>
                            <input type="hidden" datatable="pe.avvioproc" id="op_pe-avvioproc-sportello" class="search text check" name="sportello" value="equal">                           
                            <input type="radio" value="SUE" id="1_pe-avvioproc-sportello" name="sportello"  data-plugins="dynamic-search">
                            <label for="1_pe-avvioproc-sportello" class="value">SUE</label><br/>
                            <input type="radio" value="SUAP" id="2_pe-avvioproc-sportello" name="sportello"  data-plugins="dynamic-search">
                            <label for="2_pe-avvioproc-sportello" class="value">SUAP</label><br/>
                            <input type="radio" value="%" id="3_pe-avvioproc-sportello" name="sportello"  data-plugins="dynamic-search">
                            <label for="3_pe-avvioproc-sportello" class="value">Tutti</label><br/>
                        </td>
                    </tr>

                    <tr id="tr-stato_istruttoria">
                        <td valign="middle">
                            <label for="stato_istruttoria" class="title">Stato Istruttoria</label><br/>
                            <input type="hidden" datatable="pe.stato_pratica" id="op_pe-stato_pratica-stato_istruttoria" class="search text check" name="stato_istruttoria" value="equal">                           
                            <input type="radio" value="inizio" id="1_pe-stato_pratica-stato_istruttoria" name="stato_istruttoria"  data-plugins="dynamic-search">
                            <label for="1_pe-stato_pratica-stato_istruttoria" class="value">In corso</label><br/>
                            <input type="radio" value="richiesta_integrazioni" id="5_pe-stato_pratica-stato_istruttoria" name="stato_istruttoria"  data-plugins="dynamic-search">
                            <label for="5_pe-stato_pratica-stato_istruttoria" class="value">Richiesta Integrazioni
                                <span class="input-color">
                                    <span class="color-box" style="background-color: #FFFF00;"></span>
                                </span>
                            </label><br/>
                            <input type="radio" value="soprintendenza" id="6_pe-stato_pratica-stato_istruttoria" name="stato_istruttoria"  data-plugins="dynamic-search">
                            <label for="6_pe-stato_pratica-stato_istruttoria" class="value">Inviata in Soprintendenza
                                <span class="input-color">
                                    <span class="color-box" style="background-color: #CC99FF;"></span>
                                </span>
                            </label><br/>
                            <input type="radio" value="chiusa" id="2_pe-stato_pratica-stato_istruttoria" name="stato_istruttoria"  data-plugins="dynamic-search">
                            <label for="2_pe-stato_pratica-stato_istruttoria" class="value">Pratica Chiusa
                                <span class="input-color">
                                    <span class="color-box" style="background-color: #83F784;"></span>
                                </span>
                            </label><br/>
                            <input type="radio" value="chiusa_istruttoria" id="3_pe-stato_pratica-stato_istruttoria" name="stato_istruttoria"  data-plugins="dynamic-search">
                            <label for="3_pe-stato_pratica-stato_istruttoria" class="value">Istruttoria Chiusa
                                <span class="input-color">
                                    <span class="color-box" style="background-color: #FFA500;"></span>
                                </span>
                            </label><br/>
                            <input type="radio" value="%" id="4_pe-stato_pratica-stato_istruttoria" name="stato_istruttoria"  data-plugins="dynamic-search">
                            <label for="4_pe-stato_pratica-stato_istruttoria" class="value">Tutti</label><br/>
                        </td>
                    </tr>
                    <tr id="tr-tipo">
                        <td valign="middle">
                            <label for="tipo" class="title">Tipo Pratica</label><br/>
                            <input type="hidden" datatable="pe.ricercaonline_avvioproc" id="op_pe-avvioproc-tipo" class="search text check" name="tipo" value="equal">                           
                            <input type="radio" value="70000" id="1_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="1_pe-avvioproc-tipo" class="value">S.C.A.</label><br/>
                            <input type="radio" value="100000" id="5_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="5_pe-avvioproc-tipo" class="value">Permesso di Costruire</label><br/>
                            <input type="radio" value="110000" id="6_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="6_pe-avvioproc-tipo" class="value">S.C.I.A.</label><br/>
                            <input type="radio" value="120000" id="2_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="2_pe-avvioproc-tipo" class="value">S.C.I.A. alternativa al PdC</label><br/>
                            <input type="radio" value="130000" id="3_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="3_pe-avvioproc-tipo" class="value">C.I.L.A.</label><br/>
                            <input type="radio" value="140000" id="4_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="4_pe-avvioproc-tipo" class="value">C.I.L.</label><br/>
                            <input type="radio" value="150000" id="7_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="7_pe-avvioproc-tipo" class="value">Autorizzazione Paesaggistica</label><br/>
                            <input type="radio" value="160000" id="8_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="8_pe-avvioproc-tipo" class="value">Accertamento di Compatibilit&agrave; Paesaggistica</label><br/>
                            <input type="radio" value="165000" id="9_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="9_pe-avvioproc-tipo" class="value">A.U.A.</label><br/>
                            <input type="radio" value="170000" id="10_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="10_pe-avvioproc-tipo" class="value">Regolarizzazioni</label><br/>
                            <input type="radio" value="200000" id="11_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="11_pe-avvioproc-tipo" class="value">Autorizzazione Sismica</label><br/>
                            <input type="radio" value="201000" id="12_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="12_pe-avvioproc-tipo" class="value">Denuncia Cemento Armato / Sismica</label><br/>
                            <input type="radio" value="210000" id="13_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="13_pe-avvioproc-tipo" class="value">S.C.I.A. Vincolo Idrogeologico</label><br/>
                            <input type="radio" value="211000" id="14_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="14_pe-avvioproc-tipo" class="value">Autorizzazione Vincolo Idrogeologico</label><br/>
                            <input type="radio" value="%" id="15_pe-avvioproc-tipo" name="tipo"  data-plugins="dynamic-search">
                            <label for="15_pe-avvioproc-tipo" class="value">Tutti</label><br/>
                        </td>
                    </tr>
-->
                </table>