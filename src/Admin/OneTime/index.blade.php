@extends('Common.index')

@section('content')
    <div class="c-debi-one-time">
        <form id="one-time">
            <label><input type="checkbox" value="{{ json_encode([
                'method' => 'process_wp2static_urls',
            ]) }}">process_wp2static_urls</label><br>
            <label><input type="checkbox" value="{{ json_encode([
                'method' => 'exec_wp2static',
            ]) }}">exec_wp2static</label><br>
            <label><input type="checkbox" disabled value="{{ json_encode([
                'method' => 'acf_field_term_sync',
            ]) }}">acf_field_term_sync</label><br>
            <label><input type="checkbox" disabled value="{{ json_encode([
                'method' => 'update_bidirectional_fields',
            ]) }}">update_bidirectional_fields</label><br>
            <label><input type="checkbox" disabled value="{{ json_encode([
                'method' => 'process_people_names',
            ]) }}">process_people_names</label><br>
            <label><input type="checkbox" disabled value="{{ json_encode([
                'method' => 'dateToDate',
            ]) }}">published_date to publication_date_published</label><br>
            <label><input type="checkbox" disabled value="{{ json_encode([
                'method' => 'resavePosts',
            ]) }}">Resave Posts</label><br>
            <label><input type="checkbox" disabled value="{{ json_encode([
                'method' => 'whatAreTheAwardRoles',
            ]) }}">What are the award roles?</label><br>
            <label><input type="checkbox" disabled value="{{ json_encode([
                'method' => 'moveDegreeCurrentPlacementToPeople',
            ]) }}">Copy Award Participant Current Placement and Degree fields to Person entities</label><br>
            <input type="submit" value="do one time" />
            <div class="status">
                <label>Current Status: </label>
                <div class="message"></div>
            </div>
        </form>
    </div>
@endsection
