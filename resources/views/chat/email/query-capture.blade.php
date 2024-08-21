<style>
    @import url('https://fonts.googleapis.com/css2?family=Lato:wght@700&display=swap');

    * {
        margin: 0px;
        border: 0px;
        font-family: 'Lato', arial, sans-serif;
        font-weight: 300;
        color: #4d4d4d;
    }

    body.emailer{
        padding: 15px 0px;
        max-width: 600px;
        margin: 0px auto;
        background-color: white !important;
    }

</style>
<div class="email-wrapper"
    style="background-color: white; width: 100%;margin: 0px auto;border: 1px solid #d5d9de;box-shadow: 0.5rem 0.5rem 1.5rem rgba(0, 0, 0, .1);border-radius: .5rem;overflow: hidden;margin-bottom: 1rem;">
    <table style="border-collapse: collapse;width: 100%;">
        <tr>
            <th style="background-color: #2d4059;color: white;font-size: 12px;text-align: left;padding: 5px 10px;">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.group', __('default/offline_queries.ui_elements_messages.group'))}}
            </th>
            <th style="background-color: #2d4059;color: white;font-size: 12px;text-align: left;padding: 5px 10px;">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.source_type', __('default/offline_queries.ui_elements_messages.source_type'))}}</th>
            <th style="background-color: #2d4059;color: white;font-size: 12px;text-align: left;padding: 5px 10px;">
            {{default_trans($organizationId.'/offline_queries.ui_elements_messages.identifier', __('default/offline_queries.ui_elements_messages.identifier'))}}</th>
            <th style="background-color: #2d4059;color: white;font-size: 12px;text-align: left;padding: 5px 10px;">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.client_query', __('default/offline_queries.ui_elements_messages.client_query'))}}
            </th>
            <th style="background-color: #2d4059;color: white;font-size: 12px;text-align: left;padding: 5px 10px;">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.status', __('default/offline_queries.ui_elements_messages.status'))}}
            </th>
            <th style="background-color: #2d4059;color: white;font-size: 12px;text-align: left;padding: 5px 10px;">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.query_date', __('default/offline_queries.ui_elements_messages.query_date'))}}</th>
        </tr>
        <tr>
            <td style="font-size: 12px;text-align: left;padding: 5px 10px; background-color:#f5f5f5;">{{ $formattedEmailData['group_name'] }}</td>
            <td style="font-size: 12px;text-align: left;padding: 5px 10px; background-color:#f5f5f5;">{{ $formattedEmailData['source_type'] }}</td>
            <td style="font-size: 12px;text-align: left;padding: 5px 10px; background-color:#f5f5f5;">{{ $formattedEmailData['identifier'] }}</td>
            <td style="font-size: 12px;text-align: left;padding: 5px 10px; background-color:#f5f5f5;">{!!$formattedEmailData['client_query'] !!}</td>
            <td style="font-size: 12px;text-align: left;padding: 5px 10px; background-color:#f5f5f5;">{{ $formattedEmailData['status'] }}</td>
            <td style="font-size: 12px;text-align: left;padding: 5px 10px; background-color:#f5f5f5;">{{ $formattedEmailData['datetime'] }}</td>
        </tr>
    </table>
</div>