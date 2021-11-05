<style>
    table, tr ,td, th{
        border: 1px solid black;
    }

</style>

<div>
    <table>
        <tr>
            <th>ID</th>
            <th>Nazwa</th>
        </tr>
    @foreach($rooms as $room)
        <tr>
            <td>{{$room->id}}</td>
            <td><a href="{{route('rooms.show',$room->id)}}">{{$room->name}}</a></td>
        </tr>
    @endforeach
    </table>
</div>
