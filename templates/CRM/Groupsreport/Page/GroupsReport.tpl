<h3>Groups Status Report</h3>
<p>{ts}This page displays groups that have problems
  Due to either using disabled custom data fields or custom data groups
  or have groups within their criteria that do{/ts}
</p>
<p>
    <table>
      <tbody>
        <tr>
          <td>Group Id</td>
          <td>Group Name</td>
          <td>Reason</td>
        </tr>
        {foreach from=$groups item=group}
          <tr>
              <td>{$group.id}</td>
              <td>{$group.title}</td>
              <td>{$group.reason}</td>
          </tr>
      {/foreach}
      </tbody>
</table>
</p>
