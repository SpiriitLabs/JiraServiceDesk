// @ts-ignore
import { Controller } from '@hotwired/stimulus';
import ApexCharts from 'apexcharts'

export default class extends Controller {
  static values = {
    usersCountPreferenceNotification: Number,
    usersCountPreferenceNotificationIssueCreated: Number,
    usersCountPreferenceNotificationIssueUpdated: Number,
    usersCountPreferenceNotificationCommentCreated: Number,
    usersCountPreferenceNotificationCommentUpdated: Number,
  };

  static targets = ["notificationPreferenceChart"];

  declare readonly usersCountPreferenceNotificationValue: number;
  declare readonly usersCountPreferenceNotificationIssueCreatedValue: number;
  declare readonly usersCountPreferenceNotificationIssueUpdatedValue: number;
  declare readonly usersCountPreferenceNotificationCommentCreatedValue: number;
  declare readonly usersCountPreferenceNotificationCommentUpdatedValue: number;
  declare readonly notificationPreferenceChartTarget: HTMLDivElement;

  connect(): void {
    console.log("🎨 Dashboard Preference Notification controller connected.");
    this.generateNotificationPreferenceChart();
  }

  public generateNotificationPreferenceChart() {
    const percentCreated = Math.round((this.usersCountPreferenceNotificationIssueCreatedValue / this.usersCountPreferenceNotificationValue) * 100);

    var options = {
			chart: {
				height: 280,
				type: "radialBar"
			},
			series: [
				Math.round((this.usersCountPreferenceNotificationIssueCreatedValue / this.usersCountPreferenceNotificationValue) * 100),
				Math.round((this.usersCountPreferenceNotificationIssueUpdatedValue / this.usersCountPreferenceNotificationValue) * 100),
				Math.round((this.usersCountPreferenceNotificationCommentCreatedValue / this.usersCountPreferenceNotificationValue) * 100),
				Math.round((this.usersCountPreferenceNotificationCommentUpdatedValue / this.usersCountPreferenceNotificationValue) * 100),
			],
			plotOptions: {
				radialBar: {
					dataLabels: {
						total: {
							show: false,
						}
					}
				}
			},
			labels: [
        'Issue Created',
        'Issue Updated',
        'Comment Created',
        'Comment Updated',
      ],
		}

		var chart = new ApexCharts(this.notificationPreferenceChartTarget, options);
		chart.render();
  }

}

