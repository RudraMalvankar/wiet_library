# Database Schema

## Admin

| Field | Type |
|-------|------|
| AdminID | int |
| Name | varchar |
| Email | varchar |
| Phone | varchar |
| Role | varchar |
| Password | varchar |


## Books

| Field | Type |
|-------|------|
| CatNo | int |
| Language | varchar |
| Format | varchar |
| CatLevel | varchar |
| Title | varchar |
| SubTitle | varchar |
| VarTitle | varchar |
| Author1 | varchar |
| Author2 | varchar |
| Author3 | varchar |
| CorpAuthor | varchar |
| Editors | varchar |
| Publisher | varchar |
| Place | varchar |
| Year | int |
| Edition | varchar |
| ISBN | varchar |
| Subject | varchar |
| Keywords | varchar |
| Country | varchar |
| UserCode | varchar |
| DateAdded | date |
| DateModified | date |
| CreatedBy | int |
| ModifiedBy | int |


## Acquisition

| Field | Type |
|-------|------|
| AcqID | int |
| CatNo | int |
| ProcessStatus | varchar |
| VolNo | varchar |
| AppNo | varchar |
| AppDate | date |
| AppMemoNo | varchar |
| AppMemoDate | date |
| CopyProposed | int |
| CopyAppd | int |
| CopyOrd | int |
| CopyRecd | int |
| CopyAccd | int |
| Library | varchar |
| ItemPrice | decimal |
| SetPrice | decimal |
| ItemCost | decimal |
| Currency | varchar |
| ChequeNo | varchar |
| ChequeDate | date |
| Committee | varchar |
| RecommendedBy | varchar |
| RecommendedDate | date |
| OrderNo | varchar |
| OrderDate | date |
| Vendor | varchar |
| ReferenceNo | varchar |
| Note | text |
| AcquisitionMode | varchar |
| InvoiceNo | varchar |
| InvoiceDate | date |
| SourceOfInfo | varchar |
| UserCode | varchar |
| DateAdded | date |
| DateModified | date |
| CreatedBy | int |
| ModifiedBy | int |


## Holding

| Field | Type |
|-------|------|
| HoldID | int |
| AccNo | varchar |
| CatNo | int |
| CopyNo | int |
| AcqID | int |
| BookNo | varchar |
| AccDate | date |
| ClassNo | varchar |
| Status | varchar |
| CopyISBN | varchar |
| VolNo | varchar |
| VolTitle | varchar |
| Pagination | varchar |
| Binding | varchar |
| AccompanyingMaterials | varchar |
| Library | varchar |
| Section | varchar |
| Collection | varchar |
| ReferenceNo | varchar |
| BarCode | varchar |
| Location | varchar |
| Remarks | text |
| UserCode | varchar |
| DateAdded | date |
| DateModified | date |
| CreatedBy | int |
| ModifiedBy | int |


## Member

| Field | Type |
|-------|------|
| MemberNo | int |
| MemberName | varchar |
| Group | varchar |
| Designation | varchar |
| Entitlement | varchar |
| Phone | varchar |
| Email | varchar |
| FinePerDay | decimal |
| AdmissionDate | date |
| Override | boolean |
| BooksIssued | int |
| ClosingDate | date |
| Status | varchar |
| CreatedBy | int |
| ModifiedBy | int |


## Student

| Field | Type |
|-------|------|
| StudentID | int |
| Photo | blob |
| Name | varchar |
| DOB | date |
| BloodGroup | varchar |
| Branch | varchar |
| ValidTill | date |
| PRN | varchar |
| AuthorizedSignature | varchar |
| Aadhaar | varchar |
| Address | text |
| Mobile | varchar |
| QRCode | varchar |
| MemberNo | int |


## Circulation

| Field | Type |
|-------|------|
| CirculationID | int |
| MemberNo | int |
| AccNo | varchar |
| IssueDate | date |
| IssueTime | time |
| DueDate | date |
| ReserveDate | date |
| UserCode | varchar |
| DateAdded | date |
| DateModified | date |
| CreatedBy | int |
| ModifiedBy | int |


## Return

| Field | Type |
|-------|------|
| ReturnID | int |
| CirculationID | int |
| MemberNo | int |
| AccNo | varchar |
| ReturnDate | date |
| ReturnTime | time |
| Condition | varchar |
| FineAmount | decimal |
| Remarks | text |
| UserCode | varchar |
| DateAdded | date |
| DateModified | date |
| CreatedBy | int |
| ModifiedBy | int |


## E_Resources

| Field | Type |
|-------|------|
| ResourceID | int |
| ResourceType | varchar |
| Title | varchar |
| Author | varchar |
| Year | int |
| Publisher | varchar |
| FilePath | varchar |
| UploadedBy | int |
| DateAdded | date |


## Footfall

| Field | Type |
|-------|------|
| FootfallID | int |
| MemberNo | int |
| Date | date |
| TimeIn | time |
| TimeOut | time |


## Analytics

| Field | Type |
|-------|------|
| ReportID | int |
| ReportType | varchar |
| GeneratedBy | int |
| GeneratedDate | date |
| FilePath | varchar |


## Recommendations

| Field | Type |
|-------|------|
| RecID | int |
| MemberNo | int |
| RecommendedBookID | int |
| Reason | text |
| DateRecommended | date |


## DropBox

| Field | Type |
|-------|------|
| DropBoxID | int |
| Name | varchar |
| Location | varchar |
| IsActive | boolean |
| LastHeartbeatAt | timestamp |
| Note | text |


## DropReturn

| Field | Type |
|-------|------|
| DropReturnID | int |
| DropBoxID | int |
| MemberNo | int |
| AccNo | varchar |
| CirculationID | int |
| ReturnID | int |
| MemberScanAt | timestamp |
| BookScanAt | timestamp |
| Outcome | varchar |
| Reason | text |
| CreatedAt | timestamp |
| ProcessedAt | timestamp |
| ProcessedBy | int |

