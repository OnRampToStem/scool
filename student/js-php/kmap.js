///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Functions related to kMap

// Tell to the student what LOS should be visited if the student is mastering the LO tag
function GetNextRecommendedLO(tag)
{
	let n=kMap.length;
	for (i=0;i<n;i++)
	{
		input=kMap[i].input;
		v1=input.split(',');
		nv1=v1.length;
		for (j1=0;j1<nv1;j1++)
		{
			if (tag==v1[j1])
			{
				output=kMap[i].output;
				console.log('Output: ' + output);
				v2=output.split(',');
				nv2=v2.length;
				str='By mastering this learning outcome, we suggest you to visit:<ul>';
				for (j2=0;j2<nv2;j2++)
				{
					taginfo=GetTagInformation(v2[j2]);
					str+='<li class="clickable_li" onclick="testFxn(this)">';
					str+=v2[j2] + '- <strong>Learning Outcome</strong>: ' + taginfo[2] 
								+ ' of <strong>Section</strong>: ' + taginfo[1] + ' of <strong>Chapter</strong>: ' + taginfo[0]; 
					str+='</li>';
				}
				str+='</ul>';
				return str;
			}
		}
	}
	return '';
}

// Tell to the student what LOs should be visited if the student is struggling with the LO tag
function GetPreviousRecommendedLO(tag)
{
	let n=kMap.length;
	for (i=0;i<n;i++)
	{
		output=kMap[i].output;
		v1=output.split(',');
		nv1=v1.length;
		for (j1=0;j1<nv1;j1++)
		{
			if (tag==v1[j1])
			{
				input=kMap[i].input;
				console.log('Output: ' + input);
				v2=input.split(',');
				nv2=v2.length;
				str='If you wish to get better at this learning outcome, we suggest you to visit:<ul>';
				for (j2=0;j2<nv2;j2++)
				{
					taginfo=GetTagInformation(v2[j2]);
					str+='<li class="clickable_li" onclick="testFxn(this)">';
					str+=v2[j2] + '- <strong>Learning Outcome</strong>: ' + taginfo[2] 
								+ ' of <strong>Section</strong>: ' + taginfo[1] + ' of <strong>Chapter</strong>: ' + taginfo[0]; 
					str+='</li>';
				}
				str+='</ul>';
				return str;
			}
		}
	}
	return '';
}
