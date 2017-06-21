<table class="lineup">
<?php foreach($lineup->performers as $performer): ?>
	<tr>
		<td>{{$performer->person()->name}} (as {{implode(', ', $performer->stagenames)}})</td>
		<td>{{implode(', ', $performer->roles)}}</td>
		<td>{{$performer->guest[0] ? '* guest' : ''}}</td>
	</tr>
<?php endforeach; ?>
</table>
