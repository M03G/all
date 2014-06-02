<div style="margin-top:-50px;">
	<table class="table table-striped table-bordered">
		<?foreach ($tpl['screens'][$tpl['sc']]['lists'] as $key => $point) {?>		
		<tr>
			<td width=300 style='font-size:20px;'><b>Борт <?=$point['pni']?>. Лист <?=$point['lid']?></b><br><?=$point['name']?></td>
			<td width=1400>
				<?foreach ($tpl['l'][$point['lid']]['oper'] as $key_oper => $oper) {?>
				<?if ($oper['idc']){
					$color = 'success';
					$colormt = 'success_mt';
				} else {
					$color = 'default';
					$colormt = 'default_mt';
				}?>
				<span class="label label-<?= $color?> oper" style="width:<?=$tpl['l'][$point['lid']]['hour'] * $oper['tr']?>%;text-align:center;" title="<?=$oper['num']?> <?=$oper['rab']?> - <?=$oper['tr']?>ч."><h5>&nbsp;</h5></span>
				<?if($oper['mt']){?>
				<span class="label label-<?= $colormt?> oper" style="width:<?=$tpl['l'][$point['lid']]['hour'] * $oper['mt']?>%;text-align:center;" title="Межоперационное время - <?=$oper['mt']?>ч."><h5>&nbsp;</h5></span>
				<?}?>
				<?}?>
			</td>
			<td style='font-size:20px;' align=center><b style='font-size:25px;'><?= $tpl['l'][$point['lid']]['compl']?>%</b><br><?= $tpl['l'][$point['lid']]['rem']?>ч.</td>
		</tr>
		<?}?>
	</table>
</div>
